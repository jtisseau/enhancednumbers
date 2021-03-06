<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville        <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010 Laurent Destailleur         <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2007 Regis Houssin               <regis.houssin@capnetworks.com>
 * Copyright (C) 2008      Raphael Bertrand (Resultic) <raphael.bertrand@resultic.fr>
 * Copyright (C) 2016      Jonathan Tisseau				<jonathan.tisseau@86dev.fr>
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
 * \file       htdocs/custom/enhancednumbers/core/modules/propale/mod_propale_enhancednumbers.php
 * \ingroup    propale
 * \brief      File containing class for numbering module EnhancedNumbers
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/propale/modules_propale.php';
dol_include_once('/enhancednumbers/lib/enhancednumbers.lib.php');
$langs->load("enhancednumbers@enhancednumbers");

/**
 * \class		mod_propale_enhancednumbers
 * \brief		Class to manage proposal numbering rules EnhancedNumbers
 */
class mod_propale_enhancednumbers extends ModeleNumRefPropales
{
	var $version = 'development';		// 'development', 'experimental', 'dolibarr'
	var $error = '';
	var $nom = 'EnhancedNumbers';

	public function __construct()
	{
		global $langs;
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
		return enhancednumbers_getSetupRow('PROPALE_ENHANCEDNUMBERS_MASK', $conf->global->PROPALE_ENHANCEDNUMBERS_MASK);
		// $form = new Form($this->db);

		// $tooltip = enhancednumbers_getTooltip();

		// $texte = $langs->trans('GenericNumRefModelDesc')."<br/>\n"
			// .'<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n"
				// .'<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n"
				// .'<input type="hidden" name="action" value="updateMask">'."\n"
				// .'<input type="hidden" name="maskconstpropal" value="PROPALE_ENHANCEDNUMBERS_MASK">'."\n"
				// .'<table class="nobordernopadding" width="100%">'."\n"
					// .'<tr>'."\n"
						// .'<td>'.$langs->trans("Mask").':</td>'."\n"
						// .'<td align="right">'."\n"
							// .$form->textwithpicto('<input type="text" class="flat" size="24" name="maskpropal" value="'.$conf->global->PROPALE_ENHANCEDNUMBERS_MASK.'">', $tooltip, 1, 'help')."\n"
						// .'</td>'."\n"
						// .'<td align="left" rowspan="2">'."\n"
							// .'&nbsp;<input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button">'."\n"
						// .'</td>'."\n"
					// .'</tr>'."\n"
				// .'</table>'."\n"
			// .'</form>'."\n";

		// return $texte;
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
	 *  Return next value
	 *
	 *  @param	Societe		$objsoc     Object third party
	 * 	@param	Propal		$propal		Object commercial proposal
	 *  @return string      			Value if OK, 0 if KO
	 */
	function getNextValue($objsoc, $propal)
	{
		global $db, $conf;

		// On defini critere recherche compteur
		$mask = $conf->global->PROPALE_ENHANCEDNUMBERS_MASK;

		if (!$mask)
		{
			$this->error = 'NotConfigured';
			return 0;
		}

		$numFinal = enhancednumbers_get_next_value($db, $mask, 'propal', 'ref', '', $objsoc, $propal->datep);

		return  $numFinal;
	}

}
