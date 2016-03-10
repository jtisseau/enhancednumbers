/* Copyright (C) 2015	Jonathan TISSEAU	<jonathan.tisseau@86dev.fr>
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
 *	\file		css/enhancednumbers.css
 *	\ingroup	enhancednumbers
 *	\brief		Defines how the help should be displayed (with oblyon the tooltip doesn't display correctly, html appears as if they where none, especially for the standrard tags (b, ul, ...)
 */

#enhancednumbers_help {}

	#enhancednumbers_help b {
		font-weight: bold;
	}
	
	#enhancednumbers_help ul {
		list-style: disc;
	}
	
	#enhancednumbers_help code {
		border: 1px solid gray;
		font-family: courier;
	}