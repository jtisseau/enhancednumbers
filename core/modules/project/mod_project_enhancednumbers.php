<?php
/* Copyright (C) 2010 Regis Houssin  <regis.houssin@capnetworks.com>
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
 *	\file       htdocs/custom/enhanccednumbers/core/modules/project/mod_project_enhancednumbers.php
 *	\ingroup    project
 *	\brief      File containing class for numbering module EnhancedNumbers
 */

require_once DOL_DOCUMENT_ROOT .'/core/modules/project/modules_project.php';


/**
 * \class		mod_project_enhancednumbers
 * \brief		Class to manage project numbering rules EnhancedNumbers
 */
class mod_project_enhancednumbers extends ModeleNumRefProjects
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

		$tooltip = $langs->trans("GenericMaskCodes", $langs->transnoentities("Project"), $langs->transnoentities("Project"));
		$tooltip .= $langs->trans("GenericMaskCodes2");
		$tooltip .= $langs->trans("GenericMaskCodes3");
		$tooltip .= $langs->trans("GenericMaskCodes4a", $langs->transnoentities("Project"), $langs->transnoentities("Project"));
		$tooltip .= $langs->trans("GenericMaskCodes5");

		$texte = $langs->trans('GenericNumRefModelDesc')."<br/>\n";
		$texte .= '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
		$texte .= '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		$texte .= '<input type="hidden" name="action" value="updateMask">';
		$texte .= '<input type="hidden" name="maskconstproject" value="PROJECT_ENHANCEDNUMBERS_MASK">';
		$texte .= '<table class="nobordernopadding" width="100%">';
		$texte .= '<tr><td>'.$langs->trans("Mask").':</td>';
		$texte .= '<td align="right">'.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskproject" value="'.$conf->global->PROJECT_ENHANCEDNUMBERS_MASK.'">', $tooltip,1,1).'</td>';
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
	*  Return next value
	*
	*  @param	Societe		$objsoc		Object third party
	*  @param   Project		$project	Object project
	*  @return  string					Value if OK, 0 if KO
	*/
    function getNextValue($objsoc, $project)
    {
		global $db, $conf;

		dol_include_once('/enhancednumbers/lib/enhancednumbers.lib.php');

		// On defini critere recherche compteur
		$mask = $conf->global->PROJECT_ENHANCEDNUMBERS_MASK;

		if (!$mask)
		{
			$this->error = 'NotConfigured';
			return 0;
		}

		return enhancednumbers_get_next_value($db, $mask, 'projet', 'ref', '', $objsoc->code_client, $project->date_c);
	}


    /**
     *  Return next reference not yet used as a reference
     *
     *  @param	Societe		$objsoc     Object third party
     *  @param  Project		$project	Object project
     *  @return string      			Next not used reference
     */
    function project_get_num($objsoc = 0, $project = '')
    {
        return $this->getNextValue($objsoc, $project);
    }
}

