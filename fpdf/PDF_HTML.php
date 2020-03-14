<?php
//HTML2PDF by Clément Lavoillotte
//ac.lavoillotte@noos.fr
//webmaster@streetpc.tk
//http://www.streetpc.tk

require_once('mc_table.php');

//function hex2dec
//returns an associative array (keys: R,G,B) from
//a hex html code (e.g. #3FE5AA)
function hex2dec($couleur = "#000000"){
	$R = substr($couleur, 1, 2);
	$rouge = hexdec($R);
	$V = substr($couleur, 3, 2);
	$vert = hexdec($V);
	$B = substr($couleur, 5, 2);
	$bleu = hexdec($B);
	$tbl_couleur = array();
	$tbl_couleur['R']=$rouge;
	$tbl_couleur['V']=$vert;
	$tbl_couleur['B']=$bleu;
	return $tbl_couleur;
}

//conversion pixel -> millimeter at 72 dpi
function px2mm($px){
	return $px*25.4/72;
}

function txtentities($html){
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip($trans);
	return strtr($html, $trans);
}
////////////////////////////////////

class PDF_HTML extends PDF_MC_Table
{
//variables of html parser
	var $B;
	var $I;
	var $U;
	var $HREF;
	var $fontList;
	var $issetfont;
	var $issetcolor;
	var $headerFcn;
	var $footerFcn;

	function __construct($orientation='P', $unit='mm', $format='A4')
	{
		//Call parent constructor
		parent::__construct($orientation,$unit,$format);
		//Initialization
		$this->B=0;
		$this->I=0;
		$this->U=0;
		$this->HREF='';
		$this->fontlist=array('arial', 'times', 'courier', 'helvetica', 'symbol');
		$this->issetfont=false;
		$this->issetcolor=false;
		$this->headerFcn=NULL;
		$this->footerFcn=NULL;
	}

	function getHeights($data) {
		$cHeights = array();
		$nb = 0;
		for($i=0;$i<count($data);$i++) {
			$nbLines = $this->nbHtmlLines(($this->widths[$i] - 2 * $this->innerBorderX), $data[$i], $this);
			$cHeight = $nbLines * $this->zeilenhoehe; //max(0, $nbLines - 1) * $this->zeilenhoehe + min(1, $nbLines) * $this->FontSizePt * 0.35;
			$nb = max($nb, $nbLines);
			$cHeights[] = $cHeight;
		}

		return array($nb * $this->zeilenhoehe, $cHeights);
	}

	// TODO: Does not seem to work for innerBorderX < 1
	function WriteHtmlRow($data, PDF_HTML &$dummy) {
		//Calculate the height of the row
		$cHeights = array();
		if($this->doCalculateHeight) {
			$nb=0;
			for($i=0;$i<count($data);$i++) {
				$nbLines = $this->nbHtmlLines(($this->widths[$i] - 2 * $this->innerBorderX), $data[$i], $dummy);
				$cHeight = $nbLines * $this->zeilenhoehe; //max(0, $nbLines - 1) * $this->zeilenhoehe + min(1, $nbLines) * $this->FontSizePt * 0.35;
				$nb = max($nb, $nbLines);
				$cHeights[] = $cHeight;
			}
			$h=$this->zeilenhoehe*$nb+2*$this->innerBorderY;
		} else {
			$h = $this->height;
		}

		if ($this->AcceptPageBreak() && $h + $this->GetY() > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter) {
			$this->AddPage($this->CurOrientation);
		}

		$initX = $this->GetX();
		$initY = $this->GetY();

		$maxY = $h + $this->GetY();

		$lAcc = 0;
		foreach ($data as $k => $v) {
			$f = $this->fills[$k];
			$va = $this->valigns[$k];
			if(is_array($this->fillColors[$k])) {
				$this->SetFillColor($this->fillColors[$k][0], $this->fillColors[$k][1], $this->fillColors[$k][2]);
			}
			//Set text color
			if(is_array($this->textColors[$k])) {
				$this->SetTextColor($this->textColors[$k][0], $this->textColors[$k][1], $this->textColors[$k][2]);
			}
			//Draw the border
			if($this->doBorder) {
				if($this->doBorder === 'B') {
					$this->Line($initX+$lAcc, $maxY, $initX+$lAcc+$this->widths[$k], $maxY);
				} else if($this->doBorder === 'LR') {
					$this->Line($initX+$lAcc, $initY, $initX+$lAcc, $maxY);
					$this->Line($initX+$lAcc+$this->widths[$k], $initY, $initX+$lAcc+$this->widths[$k], $maxY);
				} else {
					$this->Rect($initX + $lAcc, $initY, $this->widths[$k], $maxY - $initY,($f?"DF":"D"));
				}
			}

			//$this->Rect($initX + $lAcc, $initY, $this->widths[$k], $maxY - $initY, );

			$this->SetXY($this->GetX(), $initY);
			$l = $this->GetX();
			$l = $l + $this->innerBorderX;
			$r = $l + $this->widths[$k] - 2 * $this->innerBorderX;

			$yOffset = $this->innerBorderY;

			if ($va == 'C') $yOffset = ($h - $cHeights[$k]) / 2;

			$this->SetXY($l, $this->GetY() + $yOffset);
			$this->WriteHtmlCell($r-$l, $v);

			$this->SetY($this->GetY() + $yOffset);

			$lAcc += $this->widths[$k];
			$this->SetX($initX + $lAcc);
		}
		$this->SetXY($initX, $initY);
		$this->Ln($h);
	}

	function nbHtmlLines($cellWidth, $html, PDF_HTML &$dummy) {
		$dummy->SetXY(0, 0);
		$dummy->WriteHtmlCell($cellWidth, $html);

		return ($dummy->GetY() / $this->zeilenhoehe) + 1;
	}

	function WriteHtmlCell($cellWidth, $html){
		$rm = $this->rMargin;
		$lm = $this->lMargin;

		$this->SetRightMargin($this->w - $this->GetX() - $cellWidth);

		$this->SetLeftMargin($this->GetX());

		$this->WriteHtml($html);
		$this->SetRightMargin($rm);
		$this->SetLeftMargin($lm);
	}

	function WriteHTML($html)
	{
		//HTML parser
		$html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
		$html=str_replace("\n",' ',$html); //remplace retour à la ligne par un espace
		$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //éclate la chaîne avec les balises
		foreach($a as $i=>$e)
		{
			if($i%2==0)
			{
				//Text
				if($this->HREF)
					$this->PutLink($this->HREF,$e);
				else
					$this->Write($this->zeilenhoehe,stripslashes(txtentities($e)));
			}
			else
			{
				//Tag
				if($e[0]=='/')
					$this->CloseTag(strtoupper(substr($e,1)));
				else
				{
					//Extract attributes
					$a2=explode(' ',$e);
					$tag=strtoupper(array_shift($a2));
					$attr=array();
					foreach($a2 as $v)
					{
						if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
							$attr[strtoupper($a3[1])]=$a3[2];
					}
					$this->OpenTag($tag,$attr);
				}
			}
		}
	}

	function OpenTag($tag, $attr)
	{
		//Opening tag
		switch($tag){
			case 'STRONG':
				$this->SetStyle('B',true);
				break;
			case 'EM':
				$this->SetStyle('I',true);
				break;
			case 'B':
			case 'I':
			case 'U':
				$this->SetStyle($tag,true);
				break;
			case 'A':
				$this->HREF=$attr['HREF'];
				break;
			case 'IMG':
				if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
					if(!isset($attr['WIDTH']))
						$attr['WIDTH'] = 0;
					if(!isset($attr['HEIGHT']))
						$attr['HEIGHT'] = 0;
					$this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
				}
				break;
			case 'TR':
			case 'BLOCKQUOTE':
			case 'BR':
				$this->Ln($this->zeilenhoehe);
				break;
			case 'P':
				$this->Ln($this->zeilenhoehe);
				break;
			case 'FONT':
				if (isset($attr['COLOR']) && $attr['COLOR']!='') {
					$coul=hex2dec($attr['COLOR']);
					$this->SetTextColor($coul['R'],$coul['V'],$coul['B']);
					$this->issetcolor=true;
				}
				if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
					$this->SetFont(strtolower($attr['FACE']));
					$this->issetfont=true;
				}
				break;
		}
	}

	function CloseTag($tag)
	{
		//Closing tag
		if($tag=='STRONG')
			$tag='B';
		if($tag=='EM')
			$tag='I';
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($tag,false);
		if($tag=='A')
			$this->HREF='';
		if($tag=='FONT'){
			if ($this->issetcolor==true) {
				$this->SetTextColor(0);
			}
			if ($this->issetfont) {
				$this->SetFont('arial');
				$this->issetfont=false;
			}
		}
	}

	function SetStyle($tag, $enable)
	{
		//Modify style and select corresponding font
		$this->$tag+=($enable ? 1 : -1);
		$style='';
		foreach(array('B','I','U') as $s)
		{
			if($this->$s>0)
				$style.=$s;
		}
		$this->SetFont('',$style);
	}

	function PutLink($URL, $txt)
	{
		//Put a hyperlink
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write(5,$txt,$URL);
		$this->SetStyle('U',false);
		$this->SetTextColor(0);
	}

	function Header() {
		if (is_callable($this->headerFcn)) {
			call_user_func($this->headerFcn, $this);
		}
	}
	function Footer() {
		if (is_callable($this->footerFcn)) {
			call_user_func($this->footerFcn, $this);
		}
	}

}//end of class
?>