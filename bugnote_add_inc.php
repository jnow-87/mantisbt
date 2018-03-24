<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bugnote add include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses bug_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

if( !defined( 'BUGNOTE_ADD_INC_ALLOW' ) ) {
	return;
}

require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );


$t_default_bugnote_view_status = config_get( 'default_bugnote_view_status' );


?>
<?php if( ( !bug_is_readonly( $f_bug_id ) ) &&
		( access_has_bug_level( config_get( 'add_bugnote_threshold' ), $f_bug_id ) ) ) { ?>
<?php # Bugnote Add Form BEGIN ?>

<a id="addbugnote"></a>

<?php
	$t_collapse_block = is_collapsed( 'bugnote_add' );
	$t_block_css = $t_collapse_block ? 'collapsed' : '';
	$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
	$t_allow_file_upload = file_allow_bug_upload( $f_bug_id );
?>
<form id="bugnoteadd"
	method="post"
	action="bugnote_add.php"
	enctype="multipart/form-data"
	<?php if( $t_allow_file_upload ) {
		echo ' class="dz dropzone-form" ';
		print_dropzone_form_data();
	} ?>
	>
	<?php echo form_security_field( 'bugnote_add' ) ?>
	<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
	<div id="bugnote_add" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-comment"></i>
				<?php echo lang_get( 'add_bugnote_title' ) ?>
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
				</a>
			</div>
		</div>

		<div class="widget-body">
		<div class="widget-main no-padding">

		<div class="table-responsive">
		<table class="table table-bordered table-condensed">
		<tbody>

		<tr>
<?php
		if( $t_allow_file_upload ) {
			$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );

?>
			<td>
				<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
				<div class="dropzone center">
					<i class="upload-icon ace-icon fa fa-cloud-upload blue fa-3x"></i><br>
					<span class="bigger-100 grey">
						<?php echo lang_get( 'dropzone_default_message' ) ?>
						<br/>
						<?php print_max_filesize( $t_max_file_size ); ?>
					</span>
					<div id="dropzone-previews-box" class="dz dropzone-previews dz-max-files-reached"></div>
				</div>
				<div class="fallback">
					<input id="ufile[]" name="ufile[]" type="file" size="50" />
				</div>
			</td>

<?php
		}
?>
			<td width="50%">
				<textarea name="bugnote_text" id="bugnote_text" class="form-control" rows="7"></textarea>
			</td>
		</tr>

<?php
	event_signal( 'EVENT_BUGNOTE_ADD_FORM', array( $f_bug_id ) );
?>


		</tbody>



		<tfoot>
		<tr>
		<td colspan=6>
			<label for="bugnote_add_view_status">
				<span class="label label-default"> <?php echo lang_get( 'private' ), ':' ?> </span>
				<input type="checkbox" class="ace" id="bugnote_add_view_status" name="private" <?php check_checked( $t_default_bugnote_view_status, VS_PRIVATE ); ?> />
				<span class="lbl"> &nbsp </span>
			</label>


<?php
			if( config_get( 'time_tracking_enabled' ) && access_has_bug_level( config_get( 'time_tracking_edit_threshold' ), $f_bug_id ) ) {
?>
				<span class="label label-default"> <?php echo lang_get( 'time_tracking' ), ':' ?> </span>
				<input type="text" name="time_tracking" class="input-xs" size="5" placeholder="hh:mm" />
				<span class="lbl"> &nbsp </span>
<?php
			}
?>


			<input type="submit" class="btn btn-primary btn-xs btn-white btn-round" value="<?php echo lang_get( 'add_bugnote_button' ) ?>" />
		</td>
		</tr>
		</tfoot>
</table>
</div>
</div>
</div>
</div>
</form>
<?php
}
