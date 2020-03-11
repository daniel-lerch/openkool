<?php
require_once('fpdf.php');

class PDF_MC_Table extends FPDF {
	var $widths;
	var $height;
	var $aligns;
	var $valigns;
	var $fills;
	var $innerBorderX;
	var $innerBorderY;
	var $doBorder = TRUE;
	var $doCalculateHeight = TRUE;
	var $zeilenhoehe = 4.5;
	var $cellBorders = 0;

	function calculateHeight($b) {
		$this->doCalculateHeight = $b;
	}

	function border($b) {
		$this->doBorder = $b;
	}

	function SetWidths($w) {
		//Set the array of column widths
		$this->widths=$w;
	}

	function SetHeight($h) {
		//Set the array of column heights
		$this->height=$h;
	}

	function SetAligns($a) {
		//Set the array of column alignments
		$this->aligns=$a;
	}

	function SetvAligns($a) {
		//Set the array of column alignments
		$this->valigns=$a;
	}

	function SetFills($f) {
		//Set the array of Cell Fillings
		$this->fills=$f;
	}

	function SetFillColors($c) {
		//Set fill colors for each column
		$colors = array();
		foreach($c as $hex) {
			$colors[] = array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
		}
		$this->fillColors = $colors;
	}

	function UnsetFillColors() {
		unset($this->fillColors);
	}

	function SetTextColors($c) {
		//Set text colors for each column
		$colors = array();
		foreach($c as $hex) {
			$colors[] = array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
		}
		$this->textColors = $colors;
	}

	function UnsetTextColors() {
		unset($this->textColors);
	}

	function SetInnerBorders($x, $y) {
		$this->innerBorderX = $x;
		$this->innerBorderY = $y;
	}

	function SetZeilenhoehe($h) {
		$this->zeilenhoehe = $h;
	}

	function SetCellBorders($mode) {
		$this->cellBorders = $mode;
	}




	function Row($data) {
		//Calculate the height of the row
		if($this->doCalculateHeight) {
			$nb=0;
			for($i=0;$i<count($data);$i++)
				$nb=max($nb,$this->NbLines(($this->widths[$i]-2*$this->innerBorderX),$data[$i]));
			$h=$this->zeilenhoehe*$nb+2*$this->innerBorderY;
		} else {
			$h = $this->height;
		}
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];
			$text_w=$w-$this->innerBorderX*2;
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			$f=$this->fills[$i];

			//TODO: Set font and colour

			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Check the vertical alignment
			switch($this->valigns[$i]) {
				case "T":
					//Vertikaler Rand addieren
					$this->SetXY($this->GetX(), $this->GetY()+$this->innerBorderY);
				break;
				case "C":
					$text_height = $this->zeilenhoehe*$this->NbLines($text_w, $data[$i]);
				$offset = ($h-$text_height)/2;
				$this->SetXY($this->GetX(), ($this->GetY()+$offset));
				break;
				case "B":
					$text_height = $this->zeilenhoehe*$this->NbLines($text_w, $data[$i]);
				$offset = $h-$text_height;
				$this->SetXY($this->GetX(), ($this->GetY()+$offset));
				//Vertikaler Rand subtrahieren
				$this->SetXY($this->GetX(), $this->GetY()-$this->innerBorderY);
				break;
			}
			//Horizontaler Rand
			switch($a) {
				case "L":
					$this->SetXY($this->GetX()+$this->innerBorderX, $this->GetY());
				break;
				case "C":
					break;
				case "R":
					$this->SetXY($this->GetX()+$this->innerBorderX, $this->GetY());
				break;
			}
			//Set fill color
			if(is_array($this->fillColors[$i])) {
				$this->SetFillColor($this->fillColors[$i][0], $this->fillColors[$i][1], $this->fillColors[$i][2]);
			}
			//Set text color
			if(is_array($this->textColors[$i])) {
				$this->SetTextColor($this->textColors[$i][0], $this->textColors[$i][1], $this->textColors[$i][2]);
			}
			//Draw the border
			if($this->doBorder) {
				if($this->doBorder === 'B') {
					$this->Line($x, ($y+$h), ($x+$w), ($y+$h));
				} else if($this->doBorder === 'LR') {
					$this->Line($x, $y, $x, ($y+$h));
					$this->Line($x+$w, $y, $x+$w, ($y+$h));
				} else {
					$this->Rect($x,$y,$w,$h,($f?"DF":"D"));
				}
			}
			//Print the text
			if(is_array($this->fillColors[$i])) {
				$this->MultiCell($text_w, $this->zeilenhoehe, $data[$i], $this->cellBorders, $a, $this->fillColors[$i]);
			} else {
				$this->MultiCell($text_w, $this->zeilenhoehe, $data[$i], $this->cellBorders, $a);
			}
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}


	function CalculateRowHeight($data) {
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines(($this->widths[$i]-2*$this->innerBorderX),$data[$i]));
		$h=$this->zeilenhoehe*$nb+2*$this->innerBorderY;
		return $h;
	}


	function CheckPageBreak($h) {
		//ko: Return if no auto page breaking
		if(!$this->AutoPageBreak) return FALSE;
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter)
			$this->AddPage($this->CurOrientation);
	}


	function NbLines($w,$txt) {
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}
}//class
?>
