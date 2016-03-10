<?php
/* Copyright (C) 2003-2007 Rodolphe Quiedeville         <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur          <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin                <regis.houssin@capnetworks.com>
 * Copyright (C) 2008      Raphael Bertrand (Resultic)  <raphael.bertrand@resultic.fr>
 * Copyright (C) 2013      Juanjo Menent				<jmenent@2byte.es>
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
 *	\file       htdocs/core/modules/fichinter/mod_fichinter_enhancednumbers.php
 *	\ingroup    fiche intervention
 *	\brief      File containing class for numbering module EnhancedNumbers
 */
require_once DOL_DOCUMENT_ROOT .'/core/modules/fichinter/modules_fichinter.php';

/**
 * \class		mod_fichinter_enhancednumbers
 * \brief		Class to manage intervention numbering rules EnhancedNumbers
 */
class mod_fichinter_enhancednumbers extends ModeleNumRefFicheinter
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
     *  @return     string      Texte descripif
     */
	function info()
    {
    	global $conf, $langs;

		$form = new Form($this->db);

		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("InterventionCard"), $langs->transnoentities("InterventionCard"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("InterventionCard"), $langs->transnoentities("InterventionCard"));
		$tooltip .= $langs->trans("GenericMaskCodes5");

		$texte = $langs->trans('GenericNumRefModelDesc')."<br/>\n";
		$texte .= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		$texte .= '<input type="hidden" name="action" value="updateMask">';
		$texte .= '<input type="hidden" name="maskconst" value="FICHINTER_ENHANCEDNUMBERS_MASK">';
		$texte .= '<table class="nobordernopadding" width="100%">';
		$texte .= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte .= '<td align="right">'.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskvalue" value="'.$conf->global->FICHINTER_ENHANCEDNUMBERS_MASK.'">', $tooltip,1,1).'</td>';
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
	 * 	Return next free value
	 *
	 *  @param	Societe		$objsoc     Object thirdparty
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
    function getNextValue($objsoc = 0, $object = '')
    {
		global $db, $conf;

		dol_include_once('/enhancednumbers/lib/enhancednumbers.lib.php');

		// On défini le critere de recherche du compteur
		$mask = $conf->global->FICHINTER_ENHANCEDNUMBERS_MASK;

		if (!$mask)
		{
			$this->error = 'NotConfigured';
			return 0;
		}

		return enhancednumbers_get_next_value($db, $mask, 'fichinter', 'ref', '', $objsoc, $object->datec);
  }


	/**
	 * 	Return next free value
	 *
     *  @param	Societe		$objsoc     Object third party
	 * 	@param	Object		$objforref	Object for number to search
     *  @return string      			Next free value
     */
    function getNumRef($objsoc, $objforref)
    {
        return $this->getNextValue($objsoc, $objforref);
    }

}

