<?php
// ----------------------------------
//  __/\\\\____________/\\\\___________________/\\\\\\\\\\\____/\\\\\\\\\\\\\___
//   _\/\\\\\\________/\\\\\\_________________/\\\/////////\\\_\/\\\/////////\\\_
//	_\/\\\//\\\____/\\\//\\\____/\\\__/\\\__\//\\\______\///__\/\\\_______\/\\\_
//	 _\/\\\\///\\\/\\\/_\/\\\___\//\\\/\\\____\////\\\_________\/\\\\\\\\\\\\\\__
//	  _\/\\\__\///\\\/___\/\\\____\//\\\\\________\////\\\______\/\\\/////////\\\_
//	   _\/\\\____\///_____\/\\\_____\//\\\____________\////\\\___\/\\\_______\/\\\_
//		_\/\\\_____________\/\\\__/\\_/\\\______/\\\______\//\\\__\/\\\_______\/\\\_
//		 _\/\\\_____________\/\\\_\//\\\\/______\///\\\\\\\\\\\/___\/\\\\\\\\\\\\\/__
//		  _\///______________\///___\////__________\///////////_____\/////////////_____
//			By toulousain79 ---> https://github.com/toulousain79/
//
//#####################################################################
//
//	Copyright (c) 2013 toulousain79 (https://github.com/toulousain79/)
//	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
//	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
//	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
//	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//	--> Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
//
//#################### FIRST LINE #####################################

require_once(WEB_INC . '/languages/' . $_SESSION['Language'] . '/' . basename(__FILE__));

global $MySB_DB, $CurrentUser;

$IsMainUser = (MainUser($CurrentUser)) ? true : false;

if(isset($_POST)==true && empty($_POST)==false) {
	$success = true;
	$args = '';

	switch ($_POST['submit']) {
		case User_TrackersList_Table_ForceRenew:
			$value = $MySB_DB->update("trackers_list", ["to_check" => '1', "cert_expiration" => '0000-00-00'], ["is_active" => '1']);

			if ( $value == 0 ) {
				$success = false;
			} else {
				$success = true;
				$args = 'FORCE';
			}

			if ( $success == true ) {
				$type = 'success';
			} else {
				$type = 'error';
				$message = Global_FailedUpdateMysbDB;
			}

			break;

		case Global_SaveChanges:
			for($i=0, $count = count($_POST['tracker_domain']);$i<$count;$i++) {
				switch ($_POST['is_active'][$i]) {
					case "1":
						$to_check = 1;
						break;
					default:
						$to_check = 0;
						break;
				}

				$value = $MySB_DB->update("trackers_list", ["is_active" => $_POST['is_active'][$i], "to_check" => $to_check], ["tracker_domain" => $_POST['tracker_domain'][$i]]);

				$result = $result+$value;
			}

			if ( $result == 0 ) {
				$success = false;
			} else {
				$success = true;
			}

			if ( $success == true ) {
				$type = 'success';
			} else {
				$type = 'error';
				$message = Global_FailedUpdateMysbDB;
			}
			break;

		default: //Delete
			if (isset($_POST['submit'])) {
				foreach($_POST['submit'] as $key => $value) {
					$result = $MySB_DB->update("trackers_list", ["to_delete" => 1], ["id_trackers_list" => $key]);
					if ( $result = 0 ) {
						$success = false;
					}
				}

				if ( $success == true ) {
					$type = 'success';
				} else {
					$type = 'error';
					$message = User_TrackersAdd_Btn_RemoveLastTracker;
				}
			}
			break;
	}

	GenerateMessage('GetTrackersCert.bsh', $type, $message, $args);
}

$TrackersList = $MySB_DB->select("trackers_list", ["id_trackers_list", "tracker", "tracker_domain", "tracker_proto", "tracker_port", "privacy", "is_ssl", "is_active", "cert_expiration"], ["to_delete" => 0],["ORDER" => ["name" => "ASC"]]);
?>

<div style="margin-top: 10px; margin-bottom: 20px;" id="scrollmenu" align="center">
<form class="form_settings" method="post" action="">

	<?php if ( $IsMainUser ) { ?>
		<input style="width:<?php echo strlen(User_TrackersList_Table_ForceRenew)*10; ?>px; margin-bottom: 10px; border-color: #47433F;" name="submit" type="submit" value="<?php echo User_TrackersList_Table_ForceRenew; ?>">
	<?php } ?>

	<?php if ( $IsMainUser ) { ?>
		<input class="submit" style="width:<?php echo strlen(Global_SaveChanges)*10; ?>px; margin-bottom: 10px;" name="submit" type="submit" value="<?php echo Global_SaveChanges; ?>">
	<?php } ?>

		<table style="border-spacing:1;">
			<tr>
				<th style="text-align:center;"><?php echo User_TrackersList_Table_Domain; ?></th>
				<th style="text-align:center;"><?php echo User_TrackersList_Table_Address; ?></th>
				<th style="text-align:center;"><?php echo User_TrackersList_Table_Privacy; ?></th>
				<th style="text-align:center;"><?php echo User_TrackersList_Table_IPv4; ?></th>
				<th style="text-align:center;"><?php echo User_TrackersList_Table_IsBanned; ?></th>
				<th style="text-align:center;"><?php echo User_TrackersList_Table_PingResult; ?></th>
				<th style="text-align:center;"><?php echo User_TrackersList_Table_IsSSL; ?></th>
				<th style="text-align:center;"><?php echo User_TrackersList_Table_Expiration; ?></th>
				<!-- <th style="text-align:center;"><?php echo User_TrackersList_Table_PglBlock; ?></th> -->
				<th style="text-align:center;"><?php echo Global_IsActive; ?></th>
<?php if ( $IsMainUser ) { ?>
				<th style="text-align:center;"><?php echo Global_Table_Delete; ?></th>
<?php } ?>
			</tr>
<?php
foreach($TrackersList as $Tracker) {
	switch ($Tracker["is_ssl"]) {
		case '0':
			$is_ssl = '	<select name="is_ssl[]" style="width:60px; background-color:#FEBABC;" disabled>
							<option value="0" selected="selected">' .Global_No. '</option>
						</select>';
			break;
		default:
			$is_ssl = '	<select name="is_ssl[]" style="width:60px; background-color:#B3FEA5;" disabled>
							<option value="1" selected="selected">' .Global_Yes. '</option>
						</select>';
			break;
	}

	switch ($Tracker["is_active"]) {
		case '0':
			if ( $IsMainUser ) {
				$is_active = '	<select name="is_active[]" style="width:60px;" class="redText" onchange="this.className=this.options[this.selectedIndex].className">
									<option value="0" selected="selected" class="redText">' .Global_No. '</option>
									<option value="1" class="greenText">' .Global_Yes. '</option>
								</select>';
			} else {
				$is_active = '	<select name="is_active[]" style="width:60px;" class="redText" disabled>
									<option value="0" selected="selected">' .Global_No. '</option>
								</select>';
			}
			break;
		default:
			if ( $IsMainUser ) {
				$is_active = '	<select name="is_active[]" style="width:60px;" class="greenText" onchange="this.className=this.options[this.selectedIndex].className">
									<option value="0" class="redText">' .Global_No. '</option>
									<option value="1" selected="selected" class="greenText">' .Global_Yes. '</option>
								</select>';
			} else {
				$is_active = '	<select name="is_active[]" style="width:60px;" class="greenText" disabled>
									<option value="1" selected="selected" class="greenText">' .Global_Yes. '</option>
								</select>';
			}
			break;
	}

	switch ($Tracker["privacy"]) {
		case 'public':
			$privacy = '<select name="privacy[]" style="width:100px; background-color:#FEBABC;" disabled>
							<option value="public" selected="selected">' .User_TrackersList_Table_PrivacyPublic. '</option>
						</select>';
			break;
		default:
			$privacy = '<select name="privacy[]" style="width:100px; background-color:#B3FEA5;" disabled>
							<option value="private" selected="selected">' .User_TrackersList_Table_PrivacyPrivate. '</option>
						</select>';
			break;
	}

	$nCount=1;
	$IPv4_List = $MySB_DB->select("trackers_list_ipv4", ["ipv4", "pgl_banned", "ping"], ["id_trackers_list" => $Tracker["id_trackers_list"]]);
	foreach($IPv4_List as $IPv4) {
		switch ($IPv4["pgl_banned"]) {
			case '0':
				$pgl_block = '	<select name="is_active[]" style="width:60px;" class="greenText" disabled>
									<option value="0" selected="selected">' .Global_No. '</option>
								</select>';
				break;
			default:
				$pgl_block = '	<select name="is_active[]" style="width:60px;" class="redText" disabled>
									<option value="1" selected="selected" class="redText">' .Global_Yes. '</option>
								</select>';
				break;
		}

		if ($nCount==1) {
			if ( $IsMainUser ) {
				$to_del = '<td><input class="submit" name="submit['. $Tracker["id_trackers_list"] .']" type="submit" value="' . Global_Delete . '" /></td>';
			} else {
				$to_del = '<td></td>';
			}
?>
			<tr>
				<td>
					<input style="width:150px;" type="hidden" name="tracker_domain[]" value="<?php echo $Tracker["tracker_domain"]; ?>" />
					<?php echo $Tracker["tracker_domain"]; ?>
				</td>
				<td>
					<input style="width:180px;" type="hidden" name="tracker[]" value="<?php echo $Tracker["tracker"]; ?>" />
					<?php echo $Tracker["tracker_proto"].'://'.$Tracker["tracker"].':'.$Tracker["tracker_port"]; ?>
				</td>
				<td><?php echo $privacy; ?></td>
				<td><?php echo $IPv4["ipv4"]; ?></td>
				<td style="text-align:center;"><?php echo $pgl_block; ?></td>
				<td><?php echo $IPv4["ping"]; ?></td>
				<td><?php echo $is_ssl; ?></td>
				<td><?php echo $Tracker["cert_expiration"]; ?></td>
				<td><?php echo $is_active; ?></td>
				<?php echo $to_del; ?>
			</tr>
<?php
		} else {
?>
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td><?php echo $IPv4["ipv4"]; ?></td>
				<td style="text-align:center;"><?php echo $pgl_block; ?></td>
				<td><?php echo $IPv4["ping"]; ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
<?php
		}
		$nCount++;
	}
} // foreach($TrackersList as $Tracker) {
?>

		</table>
		<?php if ( $IsMainUser ) { ?>
			<input class="submit" style="width:<?php echo strlen(Global_SaveChanges)*10; ?>px; margin-top: 10px;" name="submit" type="submit" value="<?php echo Global_SaveChanges; ?>">
		<?php } ?>
</form>
</div>

<?php
//#################### LAST LINE ######################################
