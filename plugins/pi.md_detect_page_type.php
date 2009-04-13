<?php
/*
===============================================================================
File: pi.md_detect_page_type.php
Thread: http://expressionengine.com/forums/viewthread/92307/
Docs: http://www.masugadesign.com/the-lab/scripts/md-detect-page-type/
Misc Related Links:
http://expressionengine.com/forums/viewthread/55700/P18/
-------------------------------------------------------------------------------
Purpose: Detect if the page you are on is a pagination, category,
monthly or yearly archive page; single entry page; or other type of custom page.
===============================================================================
*/
$plugin_info = array(
						'pi_name'			=> 'MD Detect Page Type',
						'pi_version'		=> '1.0.1',
						'pi_author'			=> 'Ryan Masuga',
						'pi_author_url'		=> 'http://www.masugadesign.com/',
						'pi_description'	=> 'Detect if the page you are on is a pagination, category, monthly or yearly archive page; single entry page; or other type of custom page.',
						'pi_usage'			=> Md_detect_page_type::usage()
					);

class Md_detect_page_type {

var $return_data = "";
	
	function Md_detect_page_type()
	{
			global $TMPL, $IN, $FNS, $PREFS;
      		$tagdata = $TMPL->tagdata;
			$conds = array();
			$category_word = $PREFS->ini("reserved_category_word");
			
			$url_segment = ($TMPL->fetch_param('url_segment') !== FALSE) ? $TMPL->fetch_param('url_segment') : end($IN->SEGS);
			$month_segment = ($TMPL->fetch_param('month_segment') !== FALSE) ? $TMPL->fetch_param('month_segment') : '';
			$triggers = ($TMPL->fetch_param('triggers') !== FALSE) ? explode('|', $TMPL->fetch_param('triggers')) : '';		
			
			if(is_array($triggers))
			{
				foreach($triggers as $condition)
				{
					$conds[$condition . '_page'] = (preg_match("/$condition/", $url_segment)) ? TRUE : FALSE;
				}
			}
			
			$conds['pagination_page'] = (preg_match('/^[P][0-9]+$/i', $url_segment)) ? TRUE : FALSE;
			$conds['category_page'] = (preg_match('/^[C][0-9]+$/i', $url_segment)) ? TRUE : FALSE;
			$conds['category_page'] = (preg_match("/$category_word/", $url_segment)) ? TRUE : FALSE;
			
			// Yearly is commented out here because it's too easy for an entry_id to register as a year
			// My guess is that more people use entry_ids in URLs than use yearly archives
			// (EE doesn't even generate yearly archive links by default)
			// $conds['yearly_archive_page'] = (preg_match("/^\d{4}$/", $url_segment)) ? TRUE : FALSE;
			
			$conds['monthly_archive_page'] = ( preg_match("/^\d{4}$/", $url_segment) && preg_match("/^\d{2}$/", $month_segment)) ? TRUE : FALSE;
			$conds['single_entry_page'] = (!in_array(TRUE, $conds)) ? TRUE : FALSE;

		// Prep output
		$tagdata = $FNS->prep_conditionals($tagdata, $conds);

		// return
		$this->return_data = $tagdata;

	}
    
// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

function usage()
{
ob_start(); 
?>
Useful if you're trying to use a single template to do paginated entries, categories and a single-entry. May have other uses - get creative!

PARAMETERS: 
The tag has three parameters:

1. url_segment - The main segment to check. [REQUIRED]
2. month_segment - The segment that will follow the main segment to indiciate the month when viewing a monthly archive. [OPTIONAL]
3. triggers - A pipe-delimited list of additional type of pages you'd like to check for in the main url_segment.

Example usage:
{exp:md_detect_page_type url_segment="{segment_3}" month_segment="{segment_4}" triggers="author|tag"}
Pagination Page: {if pagination_page}This is a Paginated Page{/if}<br />
Category Page: {if category_page}This is a Category Page{/if}<br />
Yearly Archive Page: {if yearly_archive_page}This is a Yearly Archive Page{/if}
Monthly Archive Page: {if monthly_archive_page}This is a Monthly Archive Page{/if}

Author Archive Page: {if author_page}This is an Author Page{/if}
Tag Page: {if tag_page}This is an Tag Page{/if}

Single Entry Page: {if segment_2 == '' AND single_entry_page}This is a Single Entry Page{/if}
{/exp:md_detect_page_type}

A single entry page is TRUE if none of the above page types are TRUE.

<?php
$buffer = ob_get_contents();
	
ob_end_clean(); 

return $buffer;
}
// END

}
?>