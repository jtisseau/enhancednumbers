<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
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
 *	\file       htdocs/core/modules/supplier_order/mod_commande_fournisseur_enhancednumbers.php
 *	\ingroup    commande
 *	\brief      File containing class for numbering module EnhancedNumbers
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/supplier_order/modules_commandefournisseur.php';


/**
 * \class		mod_commande_fournisseur_enhancednumbers
 * \brief		Class to manage supplier order numbering rules EnhancedNumbers
 */
class mod_commande_fournisseur_enhancednumbers extends ModeleNumRefSuppliersOrders
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
     *  Renvoi la description du modele de numerotation
     *
     * 	@return     string      Texte descripif
     */
	function info()
    {
    	global $conf, $langs;

		$form = new Form($this->db);

		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("Order"), $langs->transnoentities("Order"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("Order"), $langs->transnoentities("Order"));
		$tooltip .= $langs->trans("GenericMaskCodes5");

		$texte = $langs->trans('GenericNumRefModelDesc')."<br/>\n"
			.'<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n"
				.'<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n"
				.'<input type="hidden" name="action" value="updateMask">'."\n"
				.'<input type="hidden" name="maskconstorder" value="COMMANDE_FOURNISSEUR_ENHANCEDNUMBERS_MASK">'."\n"
				.'<table class="nobordernopadding" width="100%">'."\n"
					.'<tr>'."\n"
						.'<td>'.$langs->trans("Mask").':</td>'."\n"
						.'<td align="right">'."\n"
							.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskorder" value="'.$conf->global->COMMANDE_FOURNISSEUR_ENHANCEDNUMBERS_MASK.'">', $tooltip, 1, 1)."\n"
						.'</td>'."\n"
						.'<td align="left" rowspan="2">'."\n"
							.'&nbsp<input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button">'."\n"
						.'</td>'."\n"
					.'</tr>'."\n"
				.'</table>'."\n"
			.'</form>'."\n";

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
	 *  @param	Societe		$objsoc     Object third party
	 *  @param  Object	    $object		Object
     *  @return string      			Value if OK, 0 if KO
	*/
    function getNextValue($objsoc = 0, $object = '')
    {
		global $db, $conf;
		
		dol_include_once('/enhancednumbers/lib/enhancednumbers.lib.php');

		// On defini critere recherche compteur
		$mask = $conf->global->COMMANDE_FOURNISSEUR_ENHANCEDNUMBERS_MASK;

		if (!$mask)
		{
			$this->error = 'NotConfigured';
			return 0;
		}

		$numFinal = enhancednumbers_get_next_value($db, $mask, 'commande_fournisseur', 'ref', '', $objsoc, $object->date_commande);

		return  $numFinal;
	}


    /**
     *  Renvoie la reference de commande suivante non utilisee
     *
	 *  @param	Societe		$objsoc     Object third party
	 *  @param  Object	    $object		Object
     *  @return string      			Texte descripif
     */
    function commande_get_num($objsoc = 0, $object = '')
    {
        return $this->getNextValue($objsoc, $object);
    }
}

