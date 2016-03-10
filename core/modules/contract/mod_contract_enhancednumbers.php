<?php
/* Copyright (C) 2011      Juanjo Menent	    <jmenent@2byte.es>
 * Copyright (C) 2016      Jonathan Tisseau		<jonathan.tisseau@86dev.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *  \file       htdocs/core/modules/contract/mod_contract_enhancednumbers.php
 *  \ingroup    contract
 *  \brief      File containing class for numbering module EnhancedNumbers
 */

require_once DOL_DOCUMENT_ROOT .'/core/modules/contract/modules_contract.php';

/**
 * \class		mod_contract_enhancednumbers
 * \brief		Class to manage contract numbering rules EnhancedNumbers
 */
class mod_contract_enhancednumbers extends ModelNumRefContracts
{
	var $version = 'development';
	var $error = '';
	var $code_auto = 1;
	var $nom = 'EnhancedNumbers';

	public function __construct()
	{
		global $langs;
		$langs->load("enhancednumbers@enhancednumbers");
		$this->nom = $langs->trans('Module586338Name');
	}

	/**
	 *	Return default description of numbering model
	 *
	 *	@return     string      text description
	 */
	function info()
    {
    	global $conf, $langs;

		$form = new Form($this->db);

		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("Contract"), $langs->transnoentities("Contract"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("Contract"), $langs->transnoentities("Contract"));
		$tooltip .= $langs->trans("GenericMaskCodes5");

		$texte = $langs->trans('GenericNumRefModelDesc')."<br/>\n";
		$texte .= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		$texte .= '<input type="hidden" name="action" value="updateMask">';
		$texte .= '<input type="hidden" name="maskconstcontract" value="CONTRACT_ENHANCEDNUMBERS_MASK">';
		$texte .= '<table class="nobordernopadding" width="100%">';
		$texte .= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte .= '<td align="right">'.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskcontract" value="'.$conf->global->CONTRACT_ENHANCEDNUMBERS_MASK.'">', $tooltip,1,1).'</td>';
		$texte .= '<td align="left" rowspan="2">&nbsp; <input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button"></td>';
		$texte .= '</tr>';
		$texte .= '</table>';
		$texte .= '</form>';

		return $texte;
    }

    /**
     *  Renvoi un exemple de numerotation
     *
     *  @return     string      Example
     */
    function getExample()
    {
     	global $conf, $langs, $mysoc;

    	$old_code_client = $mysoc->code_client;
		$old_type_client = $mysoc->typent_code;
		
    	$mysoc->code_client = 'CCCCCCCCCC';
    	$mysoc->typent_code = 'TTTTTTTTTT';
		
     	$numExample = $this->getNextValue($mysoc, '');
		
		$mysoc->code_client = $old_code_client;
		$mysoc->typent_code = $old_type_client;

		if (!$numExample)
		{
			$numExample = 'NotConfigured';
		}
		return $numExample;
    }

	/**
	 *	Return next value
	 *
	 *	@param	Societe		$objsoc     third party object
	 *	@param	Object		$contract	contract object
	 *	@return string      			Value if OK, 0 if KO
	 */
    function getNextValue($objsoc, $contract)
    {
		global $db, $conf;

		dol_include_once('/enhancednumbers/lib/enhancednumbers.lib.php');

		$mask = $conf->global->CONTRACT_ENHANCEDNUMBERS_MASK;

		if (!$mask)
		{
			$this->error = 'NotConfigured';
			return 0;
		}

		return enhancednumbers_get_next_value($db, $mask, 'contrat', 'ref', '', $objsoc, $contract->date_contrat);
	}

	/**
	 *	Return next value
	 *
	 *	@param	Societe		$objsoc     third party object
	 *	@param	Object		$objforref	contract object
	 *	@return string      			Value if OK, 0 if KO
	 */
    function contract_get_num($objsoc, $objforref)
    {
        return $this->getNextValue($objsoc, $objforref);
    }

}

