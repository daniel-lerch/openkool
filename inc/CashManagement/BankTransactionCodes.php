<?php
/**
 * sources:
 * - https://www.iso20022.org/sites/default/files/documents/External_code_lists/BTC_Codification_23October2017.xls
 * - https://www.six-interbank-clearing.com/dam/downloads/de/standardization/iso/swiss-recommendations/implementation-guidelines-camt.pdf
 * - https://www.six-interbank-clearing.com/dam/downloads/fr/standardization/iso/swiss-recommendations/implementation-guidelines-camt.pdf
 */

return [
	'ACMT' => [
		'en' => 'Account Management',
		'fam' => [
			'ACOP' => [
				'en' => 'Additional  Miscellaneous Credit Operations',
				'sub' => [
					'PSTE' => [
						'en' => 'Posting Error',
					],
					'BCKV' => [
						'en' => 'Back Value',
					],
					'ERTA' => [
						'en' => 'Exchange Rate Adjustment',
					],
					'FLTA' => [
						'en' => 'Float Adjustment',
					],
					'VALD' => [
						'en' => 'Value Date',
					],
					'YTDA' => [
						'en' => 'YTD Adjustment',
					],
				],
			],
			'ADOP' => [
				'en' => 'Additional Miscellaneous Debit Operations',
				'sub' => [
					'BCKV' => [
						'en' => 'Back Value',
					],
					'ERTA' => [
						'en' => 'Exchange Rate Adjustment',
					],
					'FLTA' => [
						'en' => 'Float Adjustment',
					],
					'PSTE' => [
						'en' => 'Posting Error',
					],
					'VALD' => [
						'en' => 'Value Date',
					],
					'YTDA' => [
						'en' => 'YTD Adjustment',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OPCL' => [
				'en' => 'Opening & Closing',
				'sub' => [
					'ACCC' => [
						'en' => 'Account Closing',
					],
					'ACCO' => [
						'en' => 'Account Opening',
					],
					'ACCT' => [
						'en' => 'Account Transfer',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
		],
	],
	'CAMT' => [
		'en' => 'Cash Management',
		'fam' => [
			'ACCB' => [
				'en' => 'Account Balancing',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'DSBR' => [
						'en' => 'Controlled Disbursement',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'XBRD' => [
						'en' => 'Cross-Border',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'ODFT' => [
						'en' => 'Overdraft',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'SWEP' => [
						'en' => 'Sweeping',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TOPG' => [
						'en' => 'Topping',
					],
					'ZABA' => [
						'en' => 'Zero Balancing',
					],
				],
			],
			'CAPL' => [
				'en' => 'Cash Pooling',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'XBRD' => [
						'en' => 'Cross-Border',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
		],
	],
	'CMDT' => [
		'en' => 'Commodities',
		'fam' => [
			'DLVR' => [
				'en' => 'Delivery',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'FTUR' => [
				'en' => 'Futures',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OPTN' => [
				'en' => 'Options',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
			'SPOT' => [
				'en' => 'Spots',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
		],
	],
	'DERV' => [
		'en' => 'Derivatives',
		'fam' => [
			'LFUT' => [
				'en' => 'Listed Derivatives - Futures',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'LOPT' => [
				'en' => 'Listed Derivatives - Options',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OBND' => [
				'en' => 'OTC Derivatives - Bonds',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OCRD' => [
				'en' => 'OTC Derivatives - Credit',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OEQT' => [
				'en' => 'OTC Derivatives - Equity',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OIRT' => [
				'en' => 'OTC Derivatives - Interest Rates',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OSED' => [
				'en' => 'OTC Derivatives - Structured Exotic Derivatives',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OSWP' => [
				'en' => 'OTC Derivatives – Swaps',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'SWCC' => [
						'en' => 'Client Owned Collateral',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'SWFP' => [
						'en' => 'Final Payment',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'SWPP' => [
						'en' => 'Partial Payment',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'SWRS' => [
						'en' => 'Reset Payment',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'SWUF' => [
						'en' => 'Upfront Payment',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
		],
	],
	'XTND' => [
		'en' => 'Extended Domain',
		'fam' => [
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
		],
	],
	'FORX' => [
		'en' => 'Foreign Exchange',
		'fam' => [
			'FWRD' => [
				'en' => 'Forwards',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'FTUR' => [
				'en' => 'Futures',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NDFX' => [
				'en' => 'Non Deliverable',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
			'SPOT' => [
				'en' => 'Spots',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'SWAP' => [
				'en' => 'Swaps',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
		],
	],
	'LDAS' => [
		'en' => 'Loans, Deposits & Syndications',
		'fam' => [
			'CSLN' => [
				'en' => 'Consumer Loans',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DDWN' => [
						'en' => 'Drawdown',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PPAY' => [
						'en' => 'Principal Payment',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RNEW' => [
						'en' => 'Renewal',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'FTDP' => [
				'en' => 'Fixed Term Deposits',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DPST' => [
						'en' => 'Deposit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RPMT' => [
						'en' => 'Repayment',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'FTLN' => [
				'en' => 'Fixed Term Loans',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DDWN' => [
						'en' => 'Drawdown',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PPAY' => [
						'en' => 'Principal Payment',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RNEW' => [
						'en' => 'Renewal',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MGLN' => [
				'en' => 'Mortgage Loans',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DDWN' => [
						'en' => 'Drawdown',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PPAY' => [
						'en' => 'Principal Payment',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RNEW' => [
						'en' => 'Renewal',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'NTDP' => [
				'en' => 'Notice Deposits',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DPST' => [
						'en' => 'Deposit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RPMT' => [
						'en' => 'Repayment',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTLN' => [
				'en' => 'Notice Loans',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DDWN' => [
						'en' => 'Drawdown',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PPAY' => [
						'en' => 'Principal Payment',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RNEW' => [
						'en' => 'Renewal',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
			'SYDN' => [
				'en' => 'Syndications',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DDWN' => [
						'en' => 'Drawdown',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PPAY' => [
						'en' => 'Principal Payment',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RNEW' => [
						'en' => 'Renewal',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
		],
	],
	'PMNT' => [
		'en' => 'Payments',
		'fam' => [
			'CNTR' => [
				'en' => 'Counter Transactions',
				'sub' => [
					'BCDP' => [
						'en' => 'Branch Deposit',
					],
					'BCWD' => [
						'en' => 'Branch Withdrawl',
					],
					'CDPT' => [
						'en' => 'Cash Deposit',
						'de' => 'Einzahlung',
						'fr' => 'Dépôt',
					],
					'CWDL' => [
						'en' => 'Cash Withdrawal',
						'de' => 'Auszahlung',
						'fr' => 'Retrait',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'CHKD' => [
						'en' => 'Check Deposit',
						'de' => 'Checkeinlösung',
						'fr' => 'Remise de chèque',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FCDP' => [
						'en' => 'Foreign Currencies Deposit',
						'de' => 'Einzahlung Fremdwährung',
						'fr' => 'Versement devise étrangère',
					],
					'FCWD' => [
						'en' => 'Foreign Currencies Withdrawal',
						'de' => 'Auszahlung Fremdwährung',
						'fr' => 'Prélèvement devise étrangère',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'MSCD' => [
						'en' => 'Miscellaneous Deposit',
					],
					'MIXD' => [
						'en' => 'Mixed Deposit',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TCDP ' => [
						'en' => 'Travellers Cheques Deposit',
					],
					'TCWD ' => [
						'en' => 'Travellers Cheques Withdrawal',
					],
				],
			],
			'CCRD' => [
				'en' => 'Customer Card Transactions',
				'sub' => [
					'CDPT' => [
						'en' => 'Cash Deposit',
						'de' => 'Einzahlung Automat',
						'fr' => 'Dépôt automate',
					],
					'CWDL' => [
						'en' => 'Cash Withdrawal',
						'de' => 'Auszahlung Automat',
						'fr' => 'Retrait automate',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'POSC ' => [
						'en' => 'Credit Card Payment',
					],
					'XBCW' => [
						'en' => 'Cross-Border Cash Withdrawal',
						'de' => 'Auszahlung Automat Ausland',
						'fr' => 'Retrait automate étranger',
					],
					'XBCP' => [
						'en' => 'Cross-Border Credit Card Payment',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'POSD ' => [
						'en' => 'Point-of-Sale (POS) Payment  - Debit Card',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					' SMRT' => [
						'en' => 'Smart-Card Payment',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'DRFT' => [
				'en' => 'Drafts',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DDFT' => [
						'en' => 'Discounted Draft',
						'de' => 'Wechsel Diskont',
						'fr' => 'Escompte (effets de commerce)',
					],
					'UDFT' => [
						'en' => 'Dishonoured/Unpaid Draft',
						'de' => 'Wechsel Rückbuchung mangels Deckung',
						'fr' => 'Redressement d\'écriture effets faute de couverture',
					],
					'DMCG' => [
						'en' => 'Draft Maturity Change',
						'de' => 'Wechsel Verlängerung',
						'fr' => 'Prolongation effet de change',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'STAM' => [
						'en' => 'Settlement At Maturity',
						'de' => 'Wechseleinlösung nach Eingang',
						'fr' => 'Paiement effet de commerce après réception',
					],
					'STLR' => [
						'en' => 'Settlement Under Reserve',
						'de' => 'Wechseleinlösung Eingang vorbehalten',
						'fr' => 'Paiement effet de commerce sous réserve de réception',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'ICCN' => [
				'en' => 'Issued Cash Concentration Transactions',
				'sub' => [
					' ACON' => [
						'en' => 'ACH Concentration',
					],
					' BACT' => [
						'en' => 'Branch Account Transfer',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					' COAT' => [
						'en' => 'Corporate Own Account Transfer',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'XICT' => [
						'en' => 'Cross-Border Intra Company Transfer',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FIOA ' => [
						'en' => 'Financial Institution Own Account Transfer',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					' ICCT' => [
						'en' => 'Intra Company Transfer',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'ICHQ' => [
				'en' => 'Issued Cheques',
				'sub' => [
					'ARPD' => [
						'en' => 'ARP Debit',
					],
					'BCHQ' => [
						'en' => 'Bank Cheque ',
						'de' => 'Bankcheck',
						'fr' => 'Chèque de banque',
					],
					'CASH' => [
						'en' => 'Cash Letter',
						'de' => 'Cash Letter',
						'fr' => 'Cash Letter',
					],
					'CSHA' => [
						'en' => 'Cash Letter Adjustment',
						'de' => 'Cash Letter Änderung',
						'fr' => 'Modification Cash Letter',
					],
					'CCCH' => [
						'en' => 'Certified Customer Cheque',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'CCHQ' => [
						'en' => 'Cheque',
						'de' => 'Check',
						'fr' => 'Chèque',
					],
					'CQRV' => [
						'en' => 'Cheque Reversal',
						'de' => 'Check Storno',
						'fr' => 'Annulation chèque',
					],
					'URCQ' => [
						'en' => 'Cheque Under Reserve',
					],
					'CLCQ' => [
						'en' => 'Circular Cheque',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CDIS' => [
						'en' => 'Controlled Disbursement',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'CRCQ' => [
						'en' => 'Crossed Cheque',
						'de' => 'Check nur zur Verrechnung',
						'fr' => 'Chèque uniquement pour compensation',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'XBCQ' => [
						'en' => 'Foreign Cheque',
						'de' => 'Check Ausland',
						'fr' => 'Chèque étranger',
					],
					'XRCQ' => [
						'en' => 'Foreign Cheque Under Reserve',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'NPCC' => [
						'en' => 'Non Presented Circular Cheques',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OPCQ ' => [
						'en' => 'Open Cheque',
					],
					'ORCQ' => [
						'en' => 'Order Cheque',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'UPCQ' => [
						'en' => 'Unpaid Cheque',
						'de' => 'Check nicht gedeckt',
						'fr' => 'Chèque non couvert',
					],
					'XPCQ' => [
						'en' => 'Unpaid Foreign Cheque',
					],
				],
			],
			'ICDT' => [
				'en' => 'Issued Credit Transfers',
				'sub' => [
					'ACOR' => [
						'en' => 'ACH Corporate Trade',
					],
					'ACDT' => [
						'en' => 'ACH Credit',
					],
					'ADBT' => [
						'en' => 'ACH Debit',
					],
					'APAC' => [
						'en' => 'ACH Pre-Authorised',
					],
					'ARET' => [
						'en' => 'ACH Return',
					],
					'AREV' => [
						'en' => 'ACH Reversal',
					],
					'ASET' => [
						'en' => 'ACH Settlement',
					],
					'ATXN' => [
						'en' => 'ACH Transaction',
					],
					'AUTT' => [
						'en' => 'Automatic Transfer',
						'de' => 'Zahlung',
						'fr' => 'Paiement',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'VCOM' => [
						'en' => 'Credit Transfer With Agreed Commercial Information',
						'de' => 'ESR-Zahlung, QR IBAN-Zahlung',
						'fr' => 'Paiement BVR, Paiement QR-IBAN',
					],
					'XBCT' => [
						'en' => 'Cross-Border Credit Transfer',
						'de' => 'Zahlung Ausland',
						'fr' => 'Paiement étranger',
					],
					'XBSA' => [
						'en' => 'Cross-Border Payroll/Salary Payment',
						'de' => 'Zahlung Ausland Salär',
						'fr' => 'Paiement étranger salaire',
					],
					'XBST' => [
						'en' => 'Cross-Border Standing Order',
						'de' => 'Dauerauftrag Ausland',
						'fr' => 'Ordre permanent étranger',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DMCT' => [
						'en' => 'Domestic Credit Transfer',
						'de' => 'Zahlung Inland (ES, IBAN, Postkontozahlung)',
						'fr' => 'Paiement en Suisse (BV, IBAN, Paiements postaux)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FICT' => [
						'en' => 'Financial Institution Credit Transfer',
						'de' => 'Zahlung FI2FI',
						'fr' => 'Paiement EF à EF',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'BOOK' => [
						'en' => 'Internal Book Transfer',
						'de' => 'Kontoübertrag',
						'fr' => 'Transfert compte à compte',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'SALA' => [
						'en' => 'Payroll/Salary Payment',
						'de' => 'Zahlung Salär',
						'fr' => 'Paiement salaire',
					],
					'PRCT' => [
						'en' => 'Priority Credit Transfer',
						'de' => 'Zahlung priorisiert',
						'fr' => 'Paiement prioritaire',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RPCR' => [
						'en' => 'Reversal Due To Payment Cancellation Request  ',
					],
					'RRTN' => [
						'en' => 'Reversal Due To Payment Return',
						'de' => 'Rückbuchung Zahlung',
						'fr' => 'Redressement d\'écriture,',
					],
					'SDVA' => [
						'en' => 'Same Day Value Credit Transfer',
					],
					'ESCT' => [
						'en' => 'SEPA Credit Transfer',
						'de' => 'SEPA-Zahlung',
						'fr' => 'Paiement SEPA',
					],
					'STDO' => [
						'en' => 'Standing Order',
						'de' => 'Dauerauftrag',
						'fr' => 'Ordre permanent',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TTLS' => [
						'en' => 'Treasury Tax And Loan Service',
					],
				],
			],
			'IDDT' => [
				'en' => 'Issued Direct Debits',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					' XBDD' => [
						'en' => 'Cross-Border Direct Debit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'PMDD ' => [
						'en' => 'Direct Debit Payment',
					],
					'URDD' => [
						'en' => 'Direct Debit Under Reserve',
						'de' => 'Zahlungsempfänger: Lastschrift Eingang vorbehalten',
						'fr' => 'Créancie: Prélèvement sous réserve de réception',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FIDD ' => [
						'en' => 'Financial Institution Direct Debit Payment',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OODD' => [
						'en' => 'One-Off Direct Debit',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PADD' => [
						'en' => 'Pre-Authorised Direct Debit',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RCDD ' => [
						'en' => 'Reversal Due To Payment Cancellation Request',
					],
					'PRDD ' => [
						'en' => 'Reversal Due To Payment Reversal  ',
					],
					'UPDD ' => [
						'en' => 'Reversal Due To Return/Unpaid Direct Debit',
					],
					'BBDD ' => [
						'en' => 'SEPA B2B Direct Debit',
					],
					'ESDD ' => [
						'en' => 'SEPA Core Direct Debit',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'IRCT' => [
				'en' => 'Issued Real-Time Credit Transfers',
				'sub' => [
					'ACOR' => [
						'en' => 'ACH Corporate Trade',
					],
					'ACDT' => [
						'en' => 'ACH Credit',
					],
					'ADBT' => [
						'en' => 'ACH Debit',
					],
					'APAC' => [
						'en' => 'ACH Pre-Authorised',
					],
					'ARET' => [
						'en' => 'ACH Return',
					],
					'AREV' => [
						'en' => 'ACH Reversal',
					],
					'ASET' => [
						'en' => 'ACH Settlement',
					],
					'ATXN' => [
						'en' => 'ACH Transaction',
					],
					'AUTT' => [
						'en' => 'Automatic Transfer',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'VCOM' => [
						'en' => 'Credit Transfer With Agreed Commercial Information',
					],
					'XBCT' => [
						'en' => 'Cross-Border Credit Transfer',
					],
					'XBSA' => [
						'en' => 'Cross-Border Payroll/Salary Payment',
					],
					'XBST' => [
						'en' => 'Cross-Border Standing Order',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DMCT' => [
						'en' => 'Domestic Credit Transfer',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FICT' => [
						'en' => 'Financial Institution Credit Transfer',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'BOOK' => [
						'en' => 'Internal Book Transfer',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'SALA' => [
						'en' => 'Payroll/Salary Payment',
					],
					'PRCT' => [
						'en' => 'Priority Credit Transfer',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RPCR' => [
						'en' => 'Reversal Due To Payment Cancellation Request  ',
					],
					'RRTN' => [
						'en' => 'Reversal Due To Payment Return',
					],
					'SDVA' => [
						'en' => 'Same Day Value Credit Transfer',
					],
					'ESCT' => [
						'en' => 'SEPA Credit Transfer',
					],
					'STDO' => [
						'en' => 'Standing Order',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TTLS' => [
						'en' => 'Treasury Tax And Loan Service',
					],
				],
			],
			'LBOX' => [
				'en' => 'Lockbox Transactions',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'LBCA' => [
						'en' => 'Credit Adjustment',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					' LBDB' => [
						'en' => 'Debit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'LBDP ' => [
						'en' => 'Deposit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCRD' => [
				'en' => 'Merchant Card Transactions',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'POSC ' => [
						'en' => 'Credit Card Payment',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					' POSP' => [
						'en' => 'Point-of-Sale (POS) Payment',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					' SMCD' => [
						'en' => 'Smart-Card Payment',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'UPCT' => [
						'en' => 'Unpaid Card Transaction',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'IADD' => [
						'en' => 'Invoice Accepted with Differed Due Date',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
			'RCCN' => [
				'en' => 'Received Cash Concentration Transactions',
				'sub' => [
					' ACON' => [
						'en' => 'ACH Concentration',
					],
					' BACT' => [
						'en' => 'Branch Account Transfer',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					' COAT' => [
						'en' => 'Corporate Own Account Transfer',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'XICT' => [
						'en' => 'Cross-Border Intra Company Transfer',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FIOA ' => [
						'en' => 'Financial Institution Own Account Transfer',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					' ICCT' => [
						'en' => 'Intra Company Transfer',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'RCHQ' => [
				'en' => 'Received Cheques',
				'sub' => [
					'ARPD' => [
						'en' => 'ARP Debit',
					],
					'BCHQ' => [
						'en' => 'Bank Cheque ',
						'de' => 'Bankcheck',
						'fr' => 'Chèque de banque',
					],
					'CASH' => [
						'en' => 'Cash Letter',
						'de' => 'Cash Letter',
						'fr' => 'Cash Letter',
					],
					'CSHA' => [
						'en' => 'Cash Letter Adjustment',
						'de' => 'Cash Letter Änderung',
						'fr' => 'Modification Cash Letter',
					],
					'CCCH' => [
						'en' => 'Certified Customer Cheque',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'CCHQ' => [
						'en' => 'Cheque',
						'de' => 'Check',
						'fr' => 'Chèque',
					],
					'CQRV' => [
						'en' => 'Cheque Reversal',
						'de' => 'Check Rückbuchung',
						'fr' => 'Redressement d\'écriture chèque',
					],
					'URCQ' => [
						'en' => 'Cheque Under Reserve',
						'de' => 'Check Eingang vorbehalten',
						'fr' => 'Chèque sous réserve de réception',
					],
					'CLCQ' => [
						'en' => 'Circular Cheque',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CDIS' => [
						'en' => 'Controlled Disbursement',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'CRCQ' => [
						'en' => 'Crossed Cheque',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'XBCQ' => [
						'en' => 'Foreign Cheque',
						'de' => 'Check Ausland',
						'fr' => 'Chèque étranger',
					],
					'XRCQ' => [
						'en' => 'Foreign Cheque Under Reserve',
						'de' => 'Check Ausland Eingang vorbehalten',
						'fr' => 'Chèque étranger sous réserve de réception',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'NPCC' => [
						'en' => 'Non Presented Circular Cheques',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OPCQ ' => [
						'en' => 'Open Cheque',
					],
					'ORCQ' => [
						'en' => 'Order Cheque',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'UPCQ' => [
						'en' => 'Unpaid Cheque',
						'de' => 'Check nicht gedeckt',
						'fr' => 'Chèque non couvert',
					],
					'XPCQ' => [
						'en' => 'Unpaid Foreign Cheque',
						'de' => 'Check Ausland nicht gedeckt',
						'fr' => 'Chèque étranger non couvert',
					],
				],
			],
			'RCDT' => [
				'en' => 'Received Credit Transfers',
				'sub' => [
					'ACOR' => [
						'en' => 'ACH Corporate Trade',
					],
					'ACDT' => [
						'en' => 'ACH Credit',
					],
					'ADBT' => [
						'en' => 'ACH Debit',
					],
					'APAC' => [
						'en' => 'ACH Pre-Authorised',
					],
					'ARET' => [
						'en' => 'ACH Return',
					],
					'AREV' => [
						'en' => 'ACH Reversal',
					],
					'ASET' => [
						'en' => 'ACH Settlement',
					],
					'ATXN' => [
						'en' => 'ACH Transaction',
						'de' => 'Interbank',
						'fr' => 'Interbancaire',
					],
					'AUTT' => [
						'en' => 'Automatic Transfer',
						'de' => 'Zahlung',
						'fr' => 'Paiement',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'VCOM' => [
						'en' => 'Credit Transfer With Agreed Commercial Information',
						'de' => 'Zahlungseingang ESR, Zahlungseingang QR-IBAN',
						'fr' => 'Réception de paiement BVR, Réception de paiement QR-IBAN',
					],
					'XBCT' => [
						'en' => 'Cross-Border Credit Transfer',
						'de' => 'Zahlungseingang Ausland',
						'fr' => 'Réception de paiement étranger',
					],
					'XBSA' => [
						'en' => 'Cross-Border Payroll/Salary Payment',
					],
					'XBST' => [
						'en' => 'Cross-Border Standing Order',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DMCT' => [
						'en' => 'Domestic Credit Transfer',
						'de' => 'Zahlungseingang',
						'fr' => 'Réception de paiement',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FICT' => [
						'en' => 'Financial Institution Credit Transfer',
						'de' => 'Zahlungseingang FI2FI',
						'fr' => 'Réception de paiement EF à EF',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'BOOK' => [
						'en' => 'Internal Book Transfer',
						'de' => 'Kontoübertrag',
						'fr' => 'Transfert compte à compte',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'SALA' => [
						'en' => 'Payroll/Salary Payment',
						'de' => 'Zahlungseingang Salär',
						'fr' => 'Réception de paiement salaire',
					],
					'PRCT' => [
						'en' => 'Priority Credit Transfer',
						'de' => 'Zahlungseingang priorisiert',
						'fr' => 'Réception de paiement prioritaire',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RPCR' => [
						'en' => 'Reversal Due To Payment Cancellation Request  ',
						'de' => 'Rückbuchung Zahlung',
						'fr' => 'Redressement d\'écriture, paiement',
					],
					'RRTN' => [
						'en' => 'Reversal Due To Payment Return',
						'de' => 'Rückbuchung Zahlung',
						'fr' => 'Redressement d\'écriture, paiement',
					],
					'SDVA' => [
						'en' => 'Same Day Value Credit Transfer',
					],
					'ESCT' => [
						'en' => 'SEPA Credit Transfer',
						'de' => 'SEPA-Überweisung',
						'fr' => 'Virement SEPA',
					],
					'STDO' => [
						'en' => 'Standing Order',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TTLS' => [
						'en' => 'Treasury Tax And Loan Service',
					],
				],
			],
			'RDDT' => [
				'en' => 'Received Direct Debits',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					' XBDD' => [
						'en' => 'Cross-Border Direct Debit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'PMDD ' => [
						'en' => 'Direct Debit',
					],
					'URDD' => [
						'en' => 'Direct Debit Under Reserve',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FIDD ' => [
						'en' => 'Financial Institution Direct Debit Payment',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OODD' => [
						'en' => 'One-Off Direct Debit',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PADD' => [
						'en' => 'Pre-Authorised Direct Debit',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RCDD ' => [
						'en' => 'Reversal Due To Payment Cancellation Request',
					],
					'PRDD ' => [
						'en' => 'Reversal Due To Payment Reversal  ',
					],
					'UPDD ' => [
						'en' => 'Reversal Due To Return/Unpaid Direct Debit',
					],
					'BBDD ' => [
						'en' => 'SEPA B2B Direct Debit',
					],
					'ESDD ' => [
						'en' => 'SEPA Core Direct Debit',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'RRCT' => [
				'en' => 'Received Real-Time Credit Transfers',
				'sub' => [
					'ACOR' => [
						'en' => 'ACH Corporate Trade',
					],
					'ACDT' => [
						'en' => 'ACH Credit',
					],
					'ADBT' => [
						'en' => 'ACH Debit',
					],
					'APAC' => [
						'en' => 'ACH Pre-Authorised',
					],
					'ARET' => [
						'en' => 'ACH Return',
					],
					'AREV' => [
						'en' => 'ACH Reversal',
					],
					'ASET' => [
						'en' => 'ACH Settlement',
					],
					'ATXN' => [
						'en' => 'ACH Transaction',
					],
					'AUTT' => [
						'en' => 'Automatic Transfer',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'VCOM' => [
						'en' => 'Credit Transfer With Agreed Commercial Information',
					],
					'XBCT' => [
						'en' => 'Cross-Border Credit Transfer',
					],
					'XBSA' => [
						'en' => 'Cross-Border Payroll/Salary Payment',
					],
					'XBST' => [
						'en' => 'Cross-Border Standing Order',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DMCT' => [
						'en' => 'Domestic Credit Transfer',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FICT' => [
						'en' => 'Financial Institution Credit Transfer',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'BOOK' => [
						'en' => 'Internal Book Transfer',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'SALA' => [
						'en' => 'Payroll/Salary Payment',
					],
					'PRCT' => [
						'en' => 'Priority Credit Transfer',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'RPCR' => [
						'en' => 'Reversal Due To Payment Cancellation Request  ',
					],
					'RRTN' => [
						'en' => 'Reversal Due To Payment Return',
					],
					'SDVA' => [
						'en' => 'Same Day Value Credit Transfer',
					],
					'ESCT' => [
						'en' => 'SEPA Credit Transfer',
					],
					'STDO' => [
						'en' => 'Standing Order',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TTLS' => [
						'en' => 'Treasury Tax And Loan Service',
					],
				],
			],
		],
	],
	'PMET' => [
		'en' => 'Precious Metal',
		'fam' => [
			'DLVR' => [
				'en' => 'Delivery',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'FTUR' => [
				'en' => 'Futures',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OPTN' => [
				'en' => 'Options',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
			'SPOT' => [
				'en' => 'Spots',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
		],
	],
	'SECU' => [
		'en' => 'Securities',
		'fam' => [
			'BLOC' => [
				'en' => 'Blocked Transactions',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission excluding taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission including taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'XCHG' => [
						'en' => 'Exchange Traded',
					],
					'XCHC' => [
						'en' => 'Exchange Traded CCP',
					],
					'XCHN' => [
						'en' => 'Exchange Traded Non-CCP',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not available',
					],
					'OTCG' => [
						'en' => 'OTC',
					],
					'OTCC' => [
						'en' => 'OTC CCP',
					],
					'OTCN' => [
						'en' => 'OTC Non-CCP',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'COLL' => [
				'en' => 'Collateral Management',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CMBO' => [
						'en' => 'Corporate Mark Broker Owned',
					],
					'CMCO' => [
						'en' => 'Corporate Mark Client Owned',
					],
					'CPRB' => [
						'en' => 'Corporate Rebate',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'EQBO' => [
						'en' => 'Equity Mark Broker Owned',
					],
					'EQCO' => [
						'en' => 'Equity Mark Client Owned',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FWBC' => [
						'en' => 'Forwards Broker Owned Collateral',
					],
					'FWCC' => [
						'en' => 'Forwards Client Owned Collateral',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'SLBC' => [
						'en' => 'Lending Broker Owned Cash Collateral',
					],
					'SLCC' => [
						'en' => 'Lending Client Owned Cash Collateral',
					],
					'MGCC' => [
						'en' => 'Margin Client Owned Cash Collateral',
					],
					'MARG' => [
						'en' => 'Margin Payments',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OPBC' => [
						'en' => 'Option Broker Owned Collateral',
					],
					'OPCC' => [
						'en' => 'Option Client Owned Collateral',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'REPU' => [
						'en' => 'Repo',
					],
					'SECB' => [
						'en' => 'Securities Borrowing',
					],
					'SECL' => [
						'en' => 'Securities Lending',
					],
					'SWBC' => [
						'en' => 'Swap Broker Owned Collateral',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TRPO' => [
						'en' => 'Triparty Repo',
					],
				],
			],
			'CORP' => [
				'en' => 'Corporate Action',
				'sub' => [
					'BONU' => [
						'en' => 'Bonus Issue/Capitalisation Issue',
					],
					'EXRI' => [
						'en' => 'Call On Intermediate Securities',
					],
					'CAPG' => [
						'en' => 'Capital Gains Distribution',
					],
					'DVCA' => [
						'en' => 'Cash Dividend',
					],
					'CSLI' => [
						'en' => 'Cash In Lieu',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CONV' => [
						'en' => 'Conversion',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DECR' => [
						'en' => 'Decrease in Value',
					],
					'DVOP' => [
						'en' => 'Dividend Option',
					],
					'DRIP' => [
						'en' => 'Dividend Reinvestment',
					],
					'DRAW' => [
						'en' => 'Drawing',
					],
					'DTCH' => [
						'en' => 'Dutch Auction',
					],
					'SHPR' => [
						'en' => 'Equity Premium Reserve',
					],
					'EXOF' => [
						'en' => 'Exchange',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'REDM' => [
						'en' => 'Final Maturity',
					],
					'MCAL' => [
						'en' => 'Full Call / Early Redemption',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'PRII' => [
						'en' => 'Interest Payment with Principles',
					],
					'LIQU' => [
						'en' => 'Liquidation Dividend / Liquidation Payment',
					],
					'MRGR' => [
						'en' => 'Merger',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'ODLT' => [
						'en' => 'Odd Lot Sale/Purchase',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PCAL' => [
						'en' => 'Partial Redemption With Reduction Of Nominal Value',
					],
					'PRED' => [
						'en' => 'Partial Redemption Without Reduction Of Nominal Value',
					],
					'PRIO' => [
						'en' => 'Priority Issue',
					],
					'BPUT' => [
						'en' => 'Put Redemption',
					],
					'RWPL' => [
						'en' => 'Redemption Withdrawing Plan',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'BIDS' => [
						'en' => 'Repurchase Offer/Issuer Bid/Reverse Rights.',
					],
					'RHTS' => [
						'en' => 'Rights Issue/Subscription Rights/Rights Offer',
					],
					'SSPL' => [
						'en' => 'Subscription Savings Plan',
					],
					'TREC' => [
						'en' => 'Tax Reclaim',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TEND ' => [
						'en' => 'Tender',
					],
					'EXWA' => [
						'en' => 'Warrant Exercise/Warrant Conversion',
					],
				],
			],
			'OTHB' => [
				'en' => 'CSD Blocked transactions',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission excluding taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission including taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'XCHG' => [
						'en' => 'Exchange Traded',
					],
					'XCHC' => [
						'en' => 'Exchange Traded CCP',
					],
					'XCHN' => [
						'en' => 'Exchange Traded Non-CCP',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not available',
					],
					'OTCG' => [
						'en' => 'OTC',
					],
					'OTCC' => [
						'en' => 'OTC CCP',
					],
					'OTCN' => [
						'en' => 'OTC Non-CCP',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'CUST' => [
				'en' => 'Custody',
				'sub' => [
					'BONU' => [
						'en' => 'Bonus Issue/Capitalisation Issue',
					],
					'EXRI' => [
						'en' => 'Call On Intermediate Securities',
					],
					'CAPG' => [
						'en' => 'Capital Gains Distribution',
					],
					'DVCA' => [
						'en' => 'Cash Dividend',
					],
					'CSLI' => [
						'en' => 'Cash In Lieu',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission excluding taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission including taxes (Generic)',
					],
					'CONV' => [
						'en' => 'Conversion',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'DECR' => [
						'en' => 'Decrease in Value',
					],
					'DVOP' => [
						'en' => 'Dividend Option',
					],
					'DRIP' => [
						'en' => 'Dividend Reinvestment',
					],
					'DRAW' => [
						'en' => 'Drawing',
					],
					'DTCH' => [
						'en' => 'Dutch Auction',
					],
					'SHPR' => [
						'en' => 'Equity Premium Reserve',
					],
					'EXOF' => [
						'en' => 'Exchange',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'REDM' => [
						'en' => 'Final Maturity',
					],
					'MCAL' => [
						'en' => 'Full Call / Early Redemption',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'PRII' => [
						'en' => 'Interest Payment with Principles',
					],
					'LIQU' => [
						'en' => 'Liquidation Dividend / Liquidation Payment',
					],
					'MRGR' => [
						'en' => 'Merger',
					],
					'COMT' => [
						'en' => 'Non Taxable commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not available',
					],
					'ODLT' => [
						'en' => 'Odd Lot Sale/Purchase',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PCAL' => [
						'en' => 'Partial Redemption with reduction of nominal value',
					],
					'PRED' => [
						'en' => 'Partial Redemption Without Reduction of Nominal Value',
					],
					'PRIO' => [
						'en' => 'Priority Issue',
					],
					'BPUT' => [
						'en' => 'Put Redemption',
					],
					'RWPL' => [
						'en' => 'Redemption Withdrawing Plan',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'BIDS' => [
						'en' => 'Repurchase offer/Issuer Bid/Reverse Rights',
					],
					'RHTS' => [
						'en' => 'Rights Issue/Subscription Rights/Rights Offer',
					],
					'SSPL' => [
						'en' => 'Subscription Savings Plan',
					],
					'TREC' => [
						'en' => 'Tax Reclaim',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TEND ' => [
						'en' => 'Tender',
					],
					'EXWA' => [
						'en' => 'Warrant Exercise/Warrant Conversion',
					],
				],
			],
			'COLC' => [
				'en' => 'Custody Collection',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission excluding taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission including taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'LACK' => [
				'en' => 'Lack',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission excluding taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission including taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'CASH' => [
				'en' => 'Miscellaneous Securities Operations',
				'sub' => [
					'BKFE' => [
						'en' => 'Bank Fees',
					],
					'ERWI' => [
						'en' => 'Borrowing Fee',
					],
					'BROK' => [
						'en' => 'Brokerage Fee',
					],
					'CHAR' => [
						'en' => 'Charge/Fees',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CLAI' => [
						'en' => 'Compensation/Claims',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'GEN2' => [
						'en' => 'Deposit/Contribution',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INFD' => [
						'en' => 'Fixed Deposit Interest Amount',
					],
					'FUTU' => [
						'en' => 'Future Variation Margin',
					],
					'FUCO' => [
						'en' => 'Futures Commission',
					],
					'RESI' => [
						'en' => 'Futures Residual Amount',
					],
					'PRIN' => [
						'en' => 'Interest Payment with Principles',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'ERWA' => [
						'en' => 'Lending Income',
					],
					'MNFE' => [
						'en' => 'Management Fees',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'OVCH' => [
						'en' => 'Overdraft Charge',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'STAM' => [
						'en' => 'Stamp Duty',
					],
					'SWAP' => [
						'en' => 'Swap Payment',
					],
					'SWEP' => [
						'en' => 'Sweep',
					],
					'TREC' => [
						'en' => 'Tax Reclaim',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TRFE' => [
						'en' => 'Transaction Fees',
					],
					'UNCO' => [
						'en' => 'Underwriting Commission',
					],
					'GEN1' => [
						'en' => 'Withdrawal/Distribution',
					],
					'WITH' => [
						'en' => 'Withholding Tax',
					],
				],
			],
			'NSET' => [
				'en' => 'Non Settled',
				'sub' => [
					'BSBO' => [
						'en' => 'Buy Sell Back',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission excluding taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission including taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'CROS' => [
						'en' => 'Cross Trade',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'ISSU' => [
						'en' => 'Depositary Receipt Issue',
					],
					'XCHG' => [
						'en' => 'Exchange Traded',
					],
					'XCHC' => [
						'en' => 'Exchange Traded CCP',
					],
					'XCHN' => [
						'en' => 'Exchange Traded Non-CCP',
					],
					'OWNE' => [
						'en' => 'External Account Transfer',
					],
					'FCTA' => [
						'en' => 'Factor Update',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INSP' => [
						'en' => 'Inspeci/Share Exchange',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'OWNI' => [
						'en' => 'Internal Account Transfer',
					],
					'NETT' => [
						'en' => 'Netting',
					],
					'NSYN' => [
						'en' => 'Non Syndicated',
					],
					'COMT' => [
						'en' => 'Non Taxable commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not available',
					],
					'OTCG' => [
						'en' => 'OTC',
					],
					'OTCC' => [
						'en' => 'OTC CCP',
					],
					'OTCN' => [
						'en' => 'OTC Non-CCP',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PAIR' => [
						'en' => 'Pair-Off',
					],
					'PLAC' => [
						'en' => 'Placement',
					],
					'PORT' => [
						'en' => 'Portfolio Move',
					],
					'PRUD' => [
						'en' => 'Principal Pay-down/pay-up',
					],
					'REDM' => [
						'en' => 'Redemption',
					],
					'REAA' => [
						'en' => 'Redemption Asset Allocation',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'REPU' => [
						'en' => 'Repo',
					],
					'RVPO' => [
						'en' => 'Reverse Repo',
					],
					'SECB' => [
						'en' => 'Securities Borrowing',
					],
					'SECL' => [
						'en' => 'Securities Lending',
					],
					'BSBC' => [
						'en' => 'Sell Buy Back',
					],
					'SUBS' => [
						'en' => 'Subscription',
					],
					'SUAA' => [
						'en' => 'Subscription Asset Allocation',
					],
					'SWIC' => [
						'en' => 'Switch',
					],
					'SYND' => [
						'en' => 'Syndicated',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TBAC' => [
						'en' => 'TBA closing',
					],
					'TRAD' => [
						'en' => 'Trade',
					],
					'TRIN' => [
						'en' => 'Transfer In',
					],
					'TOUT' => [
						'en' => 'Transfer Out',
					],
					'TRPO' => [
						'en' => 'Triparty Repo',
					],
					'TRVO' => [
						'en' => 'Triparty Reverse Repo',
					],
					'TURN' => [
						'en' => 'Turnaround',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
			'SETT' => [
				'en' => 'Trade, Clearing and Settlement',
				'sub' => [
					'BSBO' => [
						'en' => 'Buy Sell Back',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'CROS' => [
						'en' => 'Cross Trade',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'ISSU' => [
						'en' => 'Depositary Receipt Issue',
					],
					'XCHG' => [
						'en' => 'Exchange Traded',
					],
					'XCHC' => [
						'en' => 'Exchange Traded CCP',
					],
					'XCHN' => [
						'en' => 'Exchange Traded Non-CCP',
					],
					'OWNE' => [
						'en' => 'External Account Transfer',
					],
					'FCTA' => [
						'en' => 'Factor Update',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INSP' => [
						'en' => 'Inspeci/Share Exchange',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'OWNI' => [
						'en' => 'Internal Account Transfer',
					],
					'NETT' => [
						'en' => 'Netting',
					],
					'NSYN' => [
						'en' => 'Non Syndicated',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTCG' => [
						'en' => 'OTC',
					],
					'OTCC' => [
						'en' => 'OTC CCP',
					],
					'OTCN' => [
						'en' => 'OTC Non-CCP',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'PAIR' => [
						'en' => 'Pair-Off',
					],
					'PLAC' => [
						'en' => 'Placement',
					],
					'PORT' => [
						'en' => 'Portfolio Move',
					],
					'PRUD' => [
						'en' => 'Principal Pay-Down/Pay-Up',
					],
					'REDM' => [
						'en' => 'Redemption',
					],
					'REAA' => [
						'en' => 'Redemption Asset Allocation',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'REPU' => [
						'en' => 'Repo',
					],
					'RVPO' => [
						'en' => 'Reverse Repo',
					],
					'SECB' => [
						'en' => 'Securities Borrowing',
					],
					'SECL' => [
						'en' => 'Securities Lending',
					],
					'BSBC' => [
						'en' => 'Sell Buy Back',
					],
					'SUBS' => [
						'en' => 'Subscription',
					],
					'SUAA' => [
						'en' => 'Subscription Asset Allocation',
					],
					'SWIC' => [
						'en' => 'Switch',
					],
					'SYND' => [
						'en' => 'Syndicated',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
					'TBAC' => [
						'en' => 'TBA Closing',
					],
					'TRAD' => [
						'en' => 'Trade',
					],
					'TRIN' => [
						'en' => 'Transfer In',
					],
					'TOUT' => [
						'en' => 'Transfer Out',
					],
					'TRPO' => [
						'en' => 'Triparty Repo',
					],
					'TRVO' => [
						'en' => 'Triparty Reverse Repo',
					],
					'TURN' => [
						'en' => 'Turnaround',
					],
				],
			],
		],
	],
	'TRAD' => [
		'en' => 'Trade Services',
		'fam' => [
			'CLNC' => [
				'en' => 'Clean Collection',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'STAC' => [
						'en' => 'Settlement After Collection',
					],
					'STLR' => [
						'en' => 'Settlement Under Reserve',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'DOCC' => [
				'en' => 'Documentary Collection',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'STAC' => [
						'en' => 'Settlement After Collection',
					],
					'STLR' => [
						'en' => 'Settlement Under Reserve',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'DCCT' => [
				'en' => 'Documentary Credit',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FRZF' => [
						'en' => 'Freeze Of Funds',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'SABG' => [
						'en' => 'Settlement Against Bank Guarantee',
					],
					'SOSE' => [
						'en' => 'Settlement Of Sight Export Document',
					],
					'SOSI' => [
						'en' => 'Settlement Of Sight Import Document',
					],
					'STLR' => [
						'en' => 'Settlement Under Reserve',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'GUAR' => [
				'en' => 'Guarantees',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'STLM' => [
						'en' => 'Settlement',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MCOP' => [
				'en' => 'Miscellaneous Credit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'MDOP' => [
				'en' => 'Miscellaneous Debit Operations',
				'sub' => [
					'ADJT' => [
						'en' => 'Adjustments (Generic)',
					],
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
			'NTAV' => [
				'en' => 'Not Available',
				'sub' => [
					'NTAV' => [
						'en' => 'Not Available',
					],
				],
			],
			'OTHR' => [
				'en' => 'Other',
				'sub' => [
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
				],
			],
			'LOCT' => [
				'en' => 'Stand-By Letter Of Credit',
				'sub' => [
					'CHRG' => [
						'en' => 'Charges (Generic)',
						'de' => 'Gebühren, Spesen',
						'fr' => 'Taxes, frais',
					],
					'COMM' => [
						'en' => 'Commission (Generic)',
					],
					'COME' => [
						'en' => 'Commission Excluding Taxes (Generic)',
					],
					'COMI' => [
						'en' => 'Commission Including Taxes (Generic)',
					],
					'CAJT' => [
						'en' => 'Credit Adjustments (Generic)',
						'de' => 'Berichtigung Haben',
						'fr' => 'Rectificationcrédit',
					],
					'DAJT' => [
						'en' => 'Debit Adjustments (Generic)',
						'de' => 'Berichtigung Soll',
						'fr' => 'Rectification débit',
					],
					'FEES' => [
						'en' => 'Fees (Generic)',
					],
					'FRZF' => [
						'en' => 'Freeze Of Funds',
					],
					'INTR' => [
						'en' => 'Interests (Generic)',
					],
					'COMT' => [
						'en' => 'Non Taxable Commissions (Generic)',
					],
					'NTAV' => [
						'en' => 'Not Available',
					],
					'OTHR' => [
						'en' => 'Other',
						'de' => 'Übrige',
						'fr' => 'Divers',
					],
					'RIMB' => [
						'en' => 'Reimbursement (Generic)',
					],
					'SABG' => [
						'en' => 'Settlement Against Bank Guarantee',
					],
					'SOSE' => [
						'en' => 'Settlement Of Sight Export Document',
					],
					'SOSI' => [
						'en' => 'Settlement Of Sight Import Document',
					],
					'STLR' => [
						'en' => 'Settlement Under Reserve',
					],
					'TAXE' => [
						'en' => 'Taxes (Generic)',
					],
				],
			],
		],
	],
];
