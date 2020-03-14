<?php
require_once('mc_table.php');

class PDF_tracking extends PDF_MC_Table {

	var $layout; //Holds the layout definition which will be used for rendering
	var $data; //Holds the data which will be used to complete the header and footer

	//Page header with table header row
	function Header() {
		$page_width = $this->layout['orientation'] == 'P' ? 210 : 297;

		$this->SetFont('font', '', $this->layout['fontsize']);
		$right_column_x = $this->layout['margin_left'] + max($this->GetStringWidth($this->data['tracking_name']), $this->GetStringWidth($this->data['label_timespan'])) + 10;

		$this->SetXY($this->layout['margin_left'], $this->layout['margin_top']);
		$this->SetFont('font', '', $this->layout['fontsize']);
		$this->Write($this->layout['lineheight_l'], $this->data['label_name'].':');

		$this->SetX($right_column_x);
		$this->SetFont('fontb', '', $this->layout['fontsize']);
		$this->Write($this->layout['lineheight_l'], $this->data['tracking_name']);

		$this->SetXY($this->layout['margin_left'], $this->GetY() + $this->layout['lineheight_l']);
		$this->SetFont('font', '', $this->layout['fontsize']);
		$this->Write($this->layout['lineheight_l'], $this->data['label_timespan'].':');

		$this->SetX($right_column_x);
		$this->SetFont('fontb', '', $this->layout['fontsize']);
		$this->Write($this->layout['lineheight_l'], $this->data['timespan']);

		if ($this->data['description'] != '') {
			$this->SetXY($this->layout['margin_left'], $this->GetY() + $this->layout['lineheight_l']);
			$this->SetFont('font', '', $this->layout['fontsize']);
			$this->Write($this->layout['lineheight_l'], $this->data['label_description'].':');

			$this->SetX($right_column_x);
			$this->SetLeftMargin($right_column_x);
			$this->Write($this->layout['lineheight_l'], $this->data['description']);
			$this->SetLeftMargin($this->layout['margin_left']);
		}

		$this->SetLineWidth($this->layout['header_linewidth']);
		$this->SetY($this->GetY() + $this->layout['lineheight_s'] * 2);
		$this->Line($this->layout['margin_left'], $this->GetY(), ($page_width - $this->layout['margin_right']), $this->GetY());
		$this->SetY($this->GetY() + $this->layout['lineheight_s']);

		//Add logo
		if ($this->data['logo_path'] != '') {
			$this->Image($this->data['logo_path'],$this->layout['logo_left'], $this->layout['logo_top'], $this->layout['logo_width'], $this->layout['logo_height']);
		}
	}//Header()


	function Footer() {
		$page_width = $this->layout['orientation'] == 'P' ? 210 : 297;

		$page_string = $this->data['label_page'].' '.$this->PageNo();
		$this->SetFont('font', '', $this->layout['fontsize']);
		/*$usable_page_width = $page_width - $this->layout['margin_left'] - $this->layout['margin_right'];
		$total_blank_space = $usable_page_width - $this->GetStringWidth($this->data['base_url']) - $this->GetStringWidth($this->data['created']) - $page_string;
		$padding = $total_blank_space / 2;*/

		$this->SetXY($this->layout['margin_left'], $this->layout['footer_y']);
		$this->Write($this->layout['lineheight_l'], $this->data['created']);
		$this->SetX($page_width / 2 - $this->GetStringWidth($page_string) / 2);
		$this->Write($this->layout['lineheight_l'], $page_string);
		$this->SetX($page_width - $this->GetStringWidth($this->data['base_url']) - $this->layout['margin_right'] - 3);
		$this->Write($this->layout['lineheight_l'], $this->data['base_url']);
	}//Footer()

}//class
?>
