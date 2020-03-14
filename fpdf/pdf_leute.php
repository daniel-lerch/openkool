<?php
require_once('mc_table.php');

class PDF_leute extends PDF_MC_Table {
	var $layout;  //Holds the layout definition which will be used for rendering


	//Page header with table header row
	function Header() {
		global $BASE_URL;

		$this->header_map["[[PageNumber]]"] = $this->PageNo();

		//Get y-position of header row
		$header_h = $this->layout["page"]["margin_top"]/3*2;

		//Draw header left, center and right
		$this->SetFont($this->layout["header"]["left"]["font"], "", $this->layout["header"]["left"]["fontsize"]);
		$this->SetZeilenhoehe($this->layout["header"]["left"]["fontsize"]/2);
		$text = strtr($this->layout["header"]["left"]["text"], $this->header_map);
		$this->Text($this->layout["page"]["margin_left"], $header_h, $text);

		$this->SetFont($this->layout["header"]["center"]["font"], "", $this->layout["header"]["center"]["fontsize"]);
		$this->SetZeilenhoehe($this->layout["header"]["center"]["fontsize"]/2);
		$text = strtr($this->layout["header"]["center"]["text"], $this->header_map);
		$width = $this->getStringWidth($text);
		$this->Text(($this->w/2-$width/2), $header_h, $text);

		$this->SetFont($this->layout["header"]["right"]["font"], "", $this->layout["header"]["right"]["fontsize"]);
		$this->SetZeilenhoehe($this->layout["header"]["right"]["fontsize"]/2);
		$text = strtr($this->layout["header"]["right"]["text"], $this->header_map);
		$width = $this->getStringWidth($text);
		$this->Text(($this->w-$width-$this->layout["page"]["margin_right"]), $header_h, $text);


		//Draw table header row
		if($this->layout["headerrow"]) {
			$this->SetFont($this->layout["headerrow"]["font"], "", $this->layout["headerrow"]["fontsize"]);
			$this->SetZeilenhoehe($this->layout["headerrow"]["fontsize"]/2);
			$this->SetFillColor($this->layout["headerrow"]["fillcolor"]);
			if(is_array($this->layout['headerrow']['aligns'])) {
				$this->SetAligns($this->layout['headerrow']['aligns']);
			}

			$fills = $row = array();
			foreach($this->layout["columns"] as $col => $colName) {
				$fills[] = 1;
				$fills_empty[] = 0;
				$row[] = $colName;
			}
			$this->SetFills($fills);
			$this->Row($row);
			$this->SetFills($fills_empty);
		}
	}//Header()


	function Footer() {
		$this->header_map["[[PageNumber]]"] = $this->PageNo();

		//Get y-position of footer row
		$footer_h = $this->h-($this->layout["page"]["margin_bottom"]/3);

		//Draw footer left, center and right
		$this->SetFont($this->layout["footer"]["left"]["font"], "", $this->layout["footer"]["left"]["fontsize"]);
		$this->SetZeilenhoehe($this->layout["footer"]["left"]["fontsize"]/2);
		$text = strtr($this->layout["footer"]["left"]["text"], $this->header_map);
		$this->Text($this->layout["page"]["margin_left"], $footer_h, $text);

		$this->SetFont($this->layout["footer"]["center"]["font"], "", $this->layout["footer"]["center"]["fontsize"]);
		$this->SetZeilenhoehe($this->layout["footer"]["center"]["fontsize"]/2);
		$text = strtr($this->layout["footer"]["center"]["text"], $this->header_map);
		$width = $this->getStringWidth($text);
		$this->Text(($this->w/2-$width/2), $footer_h, $text);

		$this->SetFont($this->layout["footer"]["right"]["font"], "", $this->layout["footer"]["right"]["fontsize"]);
		$this->SetZeilenhoehe($this->layout["footer"]["right"]["fontsize"]/2);
		$text = strtr($this->layout["footer"]["right"]["text"], $this->header_map);
		$width = $this->getStringWidth($text);
		$this->Text(($this->w-$width-$this->layout["page"]["margin_right"]), $footer_h, $text);
	}//Footer()

}//class
?>
