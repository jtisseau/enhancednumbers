<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville        <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur         <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2007 Regis Houssin               <regis.houssin@capnetworks.com>
 * Copyright (C) 2008      Raphael Bertrand (Resultic) <raphael.bertrand@resultic.fr>
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
 * \file       htdocs/core/modules/propale/mod_askpricesupplier_enhancednumbers.php
 * \ingroup    propale
 * \brief      File containing class for numbering module EnhancedNumbers
 */

require_once DOL_DOCUMENT_ROOT .'/core/modules/askpricesupplier/modules_askpricesupplier.php';
dol_include_once('/enhancednumbers/lib/enhancednumbers.lib.php');

/**
 * \class		mod_supplier_order_enhancednumbers
 * \brief		Class to manage ask price supplier numbering rules EnhancedNumbers
 */
class mod_askpricesupplier_enhancednumbers extends ModeleNumRefAskPriceSupplier
{
	var $version = 'development';		// 'development', 'experimental', 'dolibarr'
	var $error = '';
	var $nom = 'EnhancedNumbers';

	public function __construct()
	{
		global $langs;
		$langs->load("enhancednumbers@enhancednumbers");
		$this->nom = $langs->trans('Module586338Name');
	}

    /**
     *  Return description of module
     *
     *  @return     string      Texte descripif
     */
	function info()
    {
    	global $conf, $langs;

		$form = new Form($this->db);

		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("CommRequest"), $langs->transnoentities("CommRequest"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("CommRequest"), $langs->transnoentities("CommRequest"));
		$tooltip .= $langs->trans("GenericMaskCodes5");

		$texte = $langs->trans('GenericNumRefModelDesc')."<br/>\n";
		$texte .= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		$texte .= '<input type="hidden" name="action" value="updateMask">';
		$texte .= '<input type="hidden" name="maskconstaskpricesupplier" value="ASKPRICESUPPLIER_SAPHIR_MASK">';
		$texte .= '<table class="nobordernopadding" width="100%">';
		$texte .= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte .= '<td align="right">'.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskaskpricesupplier" value="'.$conf->global->ASKPRICESUPPLIER_SAPHIR_MASK.'">', $tooltip,1,1).'</td>';
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

    	$old_code_client = $mysoc->code_fournisseur;
		$old_type_client = $mysoc->typent_code;
		
    	$mysoc->code_fournisseur = 'FFFFFFFFFF';
    	$mysoc->typent_code = 'TTTTTTTTTT';
		
     	$numExample = $this->getNextValue($mysoc, '');
		
		$mysoc->code_fournisseur = $old_code_client;
		$mysoc->typent_code = $old_type_client;

		if (!$numExample)
		{
			$numExample = 'NotConfigured';
		}
		return $numExample;
    }

	/**
	 *  Return next value
	 *
	 *  @param	Societe		$objsoc     		Object third party
	 * 	@param	Propal		$askpricesupplier	Object askpricesupplier
	 *  @return string      					Value if OK, 0 if KO
	 */
	function getNextValue($objsoc, $askpricesupplier)
	{
		global $db, $conf;

		dol_include_once('/enhancednumbers/lib/enhancednumbers.lib.php');

		// On defini critere recherche compteur
		$mask = $conf->global->ASKPRICESUPPLIER_SAPHIR_MASK;

		if (!$mask)
		{
			$this->error = 'NotConfigured';
			return 0;
		}

		$date = $askpricesupplier->datep;
		$customercode = $objsoc->code_client;
		$numfinal = enhancednumbers_get_next_value($db, $mask, 'askpricesupplier', 'ref', '', $customercode, $date);

		return  $numFinal;
	}

}
