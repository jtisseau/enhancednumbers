<?php
/* EnhancedNumbers gives a more precise control over the numbers generation
 * Copyright (C) 2015  Jonathan TISSEAU <jonathan.tisseau@86dev.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/enhancednumbers.lib.php
 *	\ingroup	enhancednumbers
 *	\brief		This file contains functions used to generate a new number.
 */

/**
 * Return last or next value for a mask (according to area we should not reset)
 *
 * @param   DoliDB		$db				Database handler
 * @param   string		$mask			Mask to use
 * @param   string		$table			Table containing field with counter
 * @param   string		$field			Field containing already used values of counter
 * @param   string		$where			To add a filter on selection (for exemple to filter on invoice types)
 * @param   Societe		$societe		The company that own the object we need a counter for
 * @param   string		$date			Date to use for the {y},{m},{d} tags.
 * @param   string		$mode			'next' for next value or 'last' for last value
 * @param   bool		$bentityon		Activate the entity filter. Default is true (for modules not compatible with multicompany)
 * @return 	string						New value (numeric) or error message
 */
function enhancednumbers_get_next_value($db,$mask,$table,$field,$where='',$societe='',$date='',$mode='next', $bentityon=true)
{
    global $conf;
	
    // Clean parameters
    if ($date == '') $date = dol_now();	// We use local year and month of PHP server to search numbers but we should use local year and month of user
	$year = 0;
	$month = 0;
	$code_counter = 0;
	$counter = 0;
	$number = $mask;
	$hasCode = false;
	$hasType = false;
	$hasYear = false;
	$hasMonth = false;
	$hasDay = false;
	$hasCounter = false;
	$title = '';
	$datecol = 'datec';
	if ($table == 'commande_fournisseur' || $table == 'commande' || $table == 'expedition' || $table == 'livraison')
		$datecol = 'date_creation';
	
    if (! is_object($societe))
	{
		$code = $societe;
		$type = '';
	}
    else if($table == 'commande_fournisseur' || $table == 'facture_fourn' )
	{
		$code = $societe->code_fournisseur;
		$type = $societe->typent_code;
	}
    else 
	{
		$code = $societe->code_client;
		$type = $societe->typent_code;
	}
	
	$regexp_code = '/\{code(?:\:(?P<length>\d+))?\}/i';
	if (preg_match($regexp_code, $mask, $result_code))
	{
		$hasCode = true;
		if ($result_code['length'])
		{
			$code = substr($code, 0, $result_code['length']);
			$code = str_pad($code, $result_code['length'], '#');
		}
		$code = dol_string_nospecial($code);
		$number = preg_replace($regexp_code, $code, $number);
	}
	
	$regexp_type = '/\{type(?:\:(?P<length>\d+))?\}/i';
	if (preg_match($regexp_type, $mask, $result_type))
	{
		$hasType = true;
		if ($result_type['length'])
		{
			$type = substr($type, 0, $result_type['length']);
			$type = str_pad($type, $result_type['length'], '#');
		}
		$type = dol_string_nospecial($type);
		$number = preg_replace($regexp_type, $type, $number);
	}
	
	$regexp_year = '/\{year(?:\:(?P<length>\d+))?\}/i';
	if (preg_match($regexp_year, $mask, $result_year))
	{
		$hasYear = true;
		if (!$result_year['length']) $result_year['length'] = 4;
		$year = date($result_year['length'] == 4 ? 'Y' : 'y', $date);
		$number = preg_replace($regexp_year, $year, $number);
	}
	
	$regexp_month = '/\{month(?:\:(?P<length>\d+))?\}/i';
	if (preg_match($regexp_month, $mask, $result_month))
	{
		$hasMonth = true;
		if (!$result_month['length']) $result_month['length'] = 2;
		$month = date('m', $date);
		if ($result_month['length'] > 1) $month = str_pad($month, $result_month['length'], '0', STR_PAD_LEFT);
		$number = preg_replace($regexp_month, $month, $number);
	}
	
	$regexp_day = '/\{day(?:\:(?P<length>\d+))?\}/i';
	if (preg_match($regexp_day, $mask, $result_day))
	{
		$hasDay = true;
		if (!$result_day['length']) $result_day['length'] = 2;
		$day = date('d', $date);
		if ($result_day['length'] > 1) $day = str_pad($day, $result_day['length'], '0', STR_PAD_LEFT);
		$number = preg_replace($regexp_day, $day, $number);
	}
	
	$regexp_counter = '/\{(?P<type>(?:code_)?counter)(?:\:(?P<length>\d+)?(?:,(?P<reset>\d+))?)?\}/i';
	if (preg_match($regexp_counter, $mask, $result_counter))
	{
		$hasCounter = true;
		$sql = 'SELECT '.$field.' val FROM '.MAIN_DB_PREFIX.$table.' WHERE '.$field.' not like \'(PROV%\'';
		if ($where) $where = array($where);
		else $where = array();
		if ($result_counter['type'] == 'code_counter')
			$where[] = $field.' like \'%'.$code.'%\'';
		
		if ($result_counter['reset'] != '')
		{
			if ($result_counter['reset'] == 0)
				$result_counter['reset'] = $conf->global->SOCIETE_FISCAL_MONTH_START;
			
			if ($result_counter['reset'] == '99')
				$where[] = 'YEAR('.$datecol.') = '.date('Y', $date).' AND MONTH('.$datecol.') = '.date('m', $date);
			else
			{
				$year_fiscal = date('Y', $date);
				$month_fiscal = $result_counter['reset'];
				if ($month_fiscal > date('m', $date))
				{
					$where[] = '((YEAR('.$datecol.') = '.($year_fiscal - 1).' AND MONTH('.$datecol.') >= '.$month_fiscal
						.') OR (year('.$datecol.') = '.$year_fiscal.' AND MONTH('.$datecol.') < '.$month_fiscal.'))';
				}
				else
					$where[] = 'YEAR('.$datecol.') = '.$year_fiscal.' AND MONTH('.$datecol.') >= '.$month_fiscal;
			}
		}
		$sql .= (count($where) > 0 ? ' AND '.implode(' AND ', $where) : '').' ORDER BY '.$datecol.' DESC LIMIT 1';
		
		$dataset=$db->query($sql);
		if ($dataset)
		{
			$obj = $db->fetch_object($dataset);
			$counter = $obj->val;
			if (!$counter)
				$counter = 0;
			else 
			{
				$maskcounter = preg_replace_callback('/\{[^\{\}]+\}|(\+|\-|\:|\.|\?|\$)/i', function ($matches) {
						if (!$matches[1]) return $matches[0];
						else return '\\'.$matches[1];
					}, $mask);
				if ($hasCode) $maskcounter = preg_replace($regexp_code, '.'.($result_code['length'] ? '{'.$result_code['length'].'}' : '+?'), $maskcounter);
				if ($hasType) $maskcounter = preg_replace($regexp_type, '.'.($result_type['length'] ? '{'.$result_type['length'].'}' : '+?'), $maskcounter);
				if ($hasYear) $maskcounter = preg_replace($regexp_year, '\d'.($result_year['length'] ? '{'.$result_year['length'].'}' : '{2}'), $maskcounter);
				if ($hasMonth) $maskcounter = preg_replace($regexp_month, '\d'.($result_month['length'] ? '{'.$result_month['length'].'}' : '{2}'), $maskcounter);
				if ($hasDay) $maskcounter = preg_replace($regexp_day, '\d'.($result_day['length'] ? '{'.$result_day['length'].'}' : '{2}'), $maskcounter);
				$maskcounter = preg_replace($regexp_counter, '(?P<counter>\d'.($result_counter['length'] ? '{'.$result_counter['length'].'}' : '+').')', $maskcounter);
				if (preg_match('/'.$maskcounter.'/', $counter, $result_maskcounter))
					$counter = $result_maskcounter['counter'];
			}
			
			if ($mode == 'next')
			{
				$counter++;
			}
			if ($result_counter['length']) $counter = str_pad($counter, $result_counter['length'], '0', STR_PAD_LEFT);
			$number = preg_replace($regexp_counter, $counter, $number);
		}
		else dol_print_error($db);
	}
	
	// $title .= 'hasCode = '.$hasCode.', length = '.$result_code['length']."\n";
	// $title .= 'hasType = '.$hasType.', length = '.$result_type['length']."\n";
	// $title .= 'hasYear = '.$hasYear.', length = '.$result_year['length']."\n";
	// $title .= 'hasMonth = '.$hasMonth.', length = '.$result_month['length']."\n";
	// $title .= 'hasDay = '.$hasDay.', length = '.$result_day['length']."\n";
	// $title .= 'hasCounter = '.$hasCounter.', type = '.$result_counter['type'].', length = '.$result_counter['length'].', reset = '.$result_counter['reset']."\n";
	// $title .= 'sql = '.$sql."\n";
	// $title .= 'db value = '.$obj->val."\n";
	// $title .= 'maskcounter = '.$maskcounter."\n";
	// $title .= 'result_maskcounter = '.($result_maskcounter ? implode(', ', $result_maskcounter) : '/')."\n";
	// $title .= 'counter = '.$counter."\n";
	// print '<span title="'.$title.'">'.$number.'</span><br/>';
	
	return $number;
}

function enhancednumbers_getHelp()
{
	global $langs;
	
	return '<div id="enhancednumbers_help">'
		.$langs->trans('EnNums_Tooltip')
		.$langs->trans('EnNums_TooltipCode')
		.$langs->trans('EnNums_TooltipType')
		.$langs->trans('EnNums_TooltipYear')
		.$langs->trans('EnNums_TooltipMonth')
		.$langs->trans('EnNums_TooltipDay')
		.$langs->trans('EnNums_TooltipCounter')
		.$langs->trans('EnNums_TooltipCodeCounter')
		.'</div>';
}

/**
 * Return setup row
 *
 * @param   string		$valuename		The mask name 
 * @param   string		$mask			The mask value
 *
 * @return	string						Html tags representing a row to setup the mask value
 */
function enhancednumbers_getSetupRow($valuename, $value)
{
	global $langs, $db;

	$form = new Form($db);

	return $langs->trans('GenericNumRefModelDesc')."<br/>\n"
		.'<form action="'.$_SERVER["PHP_SELF"].'" method="POST">'."\n"
			.'<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">'."\n"
			.'<input type="hidden" name="action" value="updateMask">'."\n"
			.'<input type="hidden" name="maskconstpropal" value="'.$valuename.'">'."\n"
			.'<table class="nobordernopadding" width="100%">'."\n"
				.'<tr>'."\n"
					.'<td>'.$langs->trans("Mask").':</td>'."\n"
					.'<td align="right">'."\n"
						.$form->textwithpicto('<input type="text" class="flat" size="24" name="maskpropal" value="'.$value.'">', enhancednumbers_getHelp(), 1, 'help', '', 1)."\n"
					.'</td>'."\n"
					.'<td align="left" rowspan="2">'."\n"
						.'&nbsp;<input type="submit" class="button" value="'.$langs->trans("Modify").'" name="Button">'."\n"
					.'</td>'."\n"
				.'</tr>'."\n"
			.'</table>'."\n"
		.'</form>'."\n";
}