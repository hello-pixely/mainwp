<?php

class MainWP_Manage_Groups {

	public static function getClassName() {
		return __CLASS__;
	}

	public static function initMenu() {
		add_submenu_page( 'mainwp_tab', __( 'Groups', 'mainwp' ), '<div class="mainwp-hidden">' . __( 'Groups', 'mainwp' ) . '</div>', 'read', 'ManageGroups', array(
			MainWP_Manage_Groups::getClassName(),
			'renderAllGroups',
		) );
	}

	public static function getGroupListContent() {

		$groups = MainWP_DB::Instance()->getGroupsAndCount();

		foreach ( $groups as $group ) {
			self::createGroupItem( $group );
		}
	}

	private static function createGroupItem( $group ) {
	?>
		<tr group-id="<?php echo esc_attr( $group->id ); ?>" class="mainwp-group-row">
			<td>
				<span class="ui text">
					<?php esc_html_e( stripslashes( $group->name ) ); ?>
				</span>
				<span class="ui mini input fluid" style="display:none;">
					<input type="text" placeholder="<?php esc_attr_e( 'Enter group name', 'mainwp' ); ?>" value="<?php esc_attr_e( $group->name ); ?>" />
				</span>
			</td>
			<td></td>
			<td class="right aligned">
				<a href="#" class="managegroups-edit ui button green mini"> <?php _e( 'Edit Group', 'mainwp' ); ?></a>
				<a href="#" class="managegroups-rename ui button mini"><?php esc_html_e( 'Rename Group', 'mainwp' ); ?></a>
				<a href="#" class="managegroups-save ui button basic green mini" style="display:none;"> <?php _e( 'Save Group Name', 'mainwp' ); ?></a>
				<a href="#" class="managegroups-delete ui button basic red mini"><?php esc_html_e( 'Delete', 'mainwp' ); ?></a>
			</td>
		</tr>
		<tr id="mainwp-group-<?php echo esc_attr( $group->id ); ?>-sites" class="mainwp-group-sites-row">
			<td colspan="3">
				<div class="ui list">
					<?php echo MainWP_Manage_Groups::getWebsiteListContent(); ?>
				</div>
			</td>
		</tr>
	<?php
	}

	public static function getWebsiteListContent() {
		$websites = MainWP_DB::Instance()->query( MainWP_DB::Instance()->getSQLWebsitesForCurrentUser() );

		while ( $websites && ( $website = @MainWP_DB::fetch_object( $websites ) ) ) {
		?>
			<div class="item ui checkbox">
				<input type="checkbox" name="sites" value="<?php echo esc_attr($website->id); ?>" id="<?php echo MainWP_Utility::getNiceURL( $website->url ); ?>" >
				<label for="<?php echo MainWP_Utility::getNiceURL( $website->url ); ?>"><?php echo MainWP_Utility::getNiceURL( $website->url ); ?></label>
			</div>
		<?php
		}
		@MainWP_DB::free_result( $websites );
	}

	public static function renderAllGroups() {
		if ( !mainwp_current_user_can( 'dashboard', 'manage_groups' ) ) {
			mainwp_do_not_have_permissions( __( 'manage groups', 'mainwp' ) );

			return;
		}

	  do_action( 'mainwp-pageheader-sites', 'ManageGroups' );
		?>

		<div id="mainwp-manage-groups" class="ui segment">

			<div id="mainwp-message-zone" style="display: none;">
				 <div class="ui message green"><?php esc_html_e( 'Selection saved successfully.', 'mainwp' ); ?></div>
			</div>
			<table id="mainwp-groups-table" class="ui table">
			  <thead>
			    <tr>
						<th colspan="3">
							<?php esc_html_e( 'Groups', 'mainwp' ); ?>
						</th>
			  	</tr>
				</thead>
				<tbody>
					<?php echo MainWP_Manage_Groups::getGroupListContent(); ?>
					<tr class="managegroups-group-add" style="display:none;">
						<td>
							<span class="ui mini input fluid"><input type="text" placeholder="<?php esc_attr_e( 'Group name', 'mainwp' ); ?>" value="" /></span>
						</td>
						<td></td>
						<td class="right aligned">
							<a href="#" class="managegroups-savenew ui button green mini"><?php _e( 'Save Group', 'mainwp' ); ?></a>
							<a href="#" class="managegroups-cancel ui button basic red mini"><?php esc_html_e( 'Cancel', 'mainwp' ); ?></a>
						</td>
					</tr>
				</tbody>
				<tfoot class="full-width">
			    <tr>
			      <th colspan="3">
							<input type="button" value="<?php esc_attr_e( 'Save Selection', 'mainwp' ); ?>" class="managegroups-saveAll ui right floated green button" style="display:none" />
							<a class="managegroups-addnew ui green basic button" href="javascript:void(0)"><?php _e( 'Create New Group', 'mainwp' ); ?></a>
			      </th>
			    </tr>
			  </tfoot>
			</table>

		</div>

		<?php do_action( 'mainwp-pagefooter-sites', 'ManageGroups' ); ?>

		<script type="text/javascript">
			jQuery( document ).ready( function () {

        jQuery( document ).on( 'click', '.managegroups-rename', function () {
					var parentObj = jQuery( this ).closest( 'tr' );
					parentObj.find( '.text' ).hide();
					parentObj.find( '.input' ).show();
					parentObj.find( '.managegroups-rename' ).hide();
					parentObj.find( '.managegroups-save' ).show();
					parentObj.addClass('active');
					return false;
				} );

        jQuery( document ).on( 'click', '.managegroups-save', function () {
					var parentObj = jQuery( this ).closest( 'tr' );
					var groupId = parentObj.attr( 'group-id' );
					var newName = parentObj.find( '.input input' ).val();

					var data = mainwp_secure_data( {
						action: 'mainwp_group_rename',
						groupId: groupId,
						newName: newName
					} );

					jQuery.post( ajaxurl, data, function ( pParentObj ) {
						return function ( response ) {
							if ( response.error )
								return;

							response = jQuery.trim( response.result );
							pParentObj.find( '.input input' ).val( response );
							pParentObj.find( '.text' ).html( response );

							pParentObj.find( '.input' ).hide();
							pParentObj.find( '.managegroups-save' ).hide();
							pParentObj.find( '.text' ).show();
							pParentObj.find( '.managegroups-rename' ).show();
							parentObj.removeClass('active');
						}
					}( parentObj ), 'json' );

					return false;
				} );

				jQuery( document ).on( 'click', '.managegroups-delete', function () {

					var msg = 'Are you sure you want to delete this sites group?';
					var me = this;
					var confirmed = function() {
						var parentObj = jQuery( me ).closest( 'tr' );
					 	parentObj.addClass( 'negative' );
						var groupId = parentObj.attr( 'group-id' );

						var data = mainwp_secure_data( {
							action: 'mainwp_group_delete',
							groupId: groupId
						} );

						jQuery.post( ajaxurl, data, function ( pParentObj ) {
							return function ( response ) {
								response = jQuery.trim( response );
								if ( response == 'OK' )
									pParentObj.animate( { opacity: 0 }, 300, function () {
										pParentObj.remove()
									} );
							}
						}( parentObj ) );
					};
					mainwp_confirm( msg, confirmed);
					return false;
				} );

                jQuery( document ).on( 'click', '.managegroups-addnew', function () {
					var addNewContainer = jQuery( '.managegroups-group-add' );
					addNewContainer.find( 'input' ).val( '' );
					addNewContainer.show();
				} );

                jQuery( document ).on( 'click', '.managegroups-cancel', function () {
					var addNewContainer = jQuery( '.managegroups-group-add' );
					addNewContainer.hide();
					addNewContainer.find( 'input' ).val( '' );
				} );

                jQuery( document ).on( 'click', '.managegroups-savenew', function () {
					var parentObj = jQuery( this ).closest( 'tr' );
					var newName = parentObj.find( 'input' ).val();

					var data = mainwp_secure_data( {
						action: 'mainwp_group_add',
						newName: newName
					} );

					jQuery.post( ajaxurl, data, function ( response ) {
						try {
							resp = jQuery.parseJSON( response );

							if ( resp.error != undefined )
								return;
						} catch ( err ) {

						}

						response = jQuery.trim( response );

						var addNewContainer = jQuery( '.managegroups-group-add' );
						addNewContainer.hide();
						addNewContainer.find( 'input' ).val( '' );

						addNewContainer.after( response );
					} );

					return false;
				} );

                jQuery( document ).on( 'click', '.managegroups-edit', function () {

                    var parentObj = jQuery( this ).closest( '.mainwp-group-row' );
                    var curActive = parentObj.hasClass('active') ? true : false;

                    jQuery('.mainwp-group-row').removeClass('active'); // remove all active
                    jQuery('.mainwp-group-sites-row').removeClass('active'); // hide all sites row

          if ( curActive ) {
                        parentObj.removeClass('active');
                        parentObj.next('.mainwp-group-sites-row').removeClass('active');
                    } else {
                        parentObj.addClass('active');
                        parentObj.next('.mainwp-group-sites-row').addClass('active');
                    }

          if ( jQuery( '.mainwp-group-row.active' ).length > 0 ) {
            jQuery( '.managegroups-saveAll' ).show();
                    } else {
            jQuery( '.managegroups-saveAll' ).hide();
                    }

					var groupId = parentObj.attr( 'group-id' );

					var data = {
						action: 'mainwp_group_getsites',
						groupId: groupId
					}

          jQuery( '.managegroups-saveAll' ).attr( "disabled", true );
					jQuery.post( ajaxurl, data, function ( response ) {
                        jQuery('.managegroups-saveAll').removeAttr("disabled");

						response = jQuery.trim( response );
						if ( response == 'ERROR' )
							return;

						jQuery( 'input[name="sites"]' ).attr( 'checked', false );

						var websiteIds = jQuery.parseJSON( response );
						for ( var i = 0; i < websiteIds.length; i++ ) {
							parentObj.next( 'tr' ).find( 'input[name="sites"][value="' + websiteIds[i] + '"]' ).attr( 'checked', true );
						}
					} );
				} );

                jQuery( document ).on( 'click', '.managegroups-saveAll', function () {
					var checkedGroup = jQuery( '#mainwp-manage-groups tr.mainwp-group-row.active' );
					var groupId = checkedGroup.attr( 'group-id' );

					if ( groupId == undefined )
						return;


					var allCheckedWebsites = jQuery( '#mainwp-manage-groups tr.mainwp-group-sites-row.active' ).find( 'input[name="sites"]:checked' );
					var allCheckedIds = [ ];
					for ( var i = 0; i < allCheckedWebsites.length; i++ ) {
						allCheckedIds.push( jQuery( allCheckedWebsites[i] ).val() );
					}

					var data = mainwp_secure_data( {
						action: 'mainwp_group_updategroup',
						groupId: groupId,
						websiteIds: allCheckedIds
					} );

                    var btn = this;
                    jQuery(btn).attr("disabled", true);
					jQuery.post( ajaxurl, data, function ( response ) {
                        jQuery(btn).removeAttr("disabled");
						jQuery( '#mainwp-message-zone' ).stop( true, true );
						jQuery( '#mainwp-message-zone' ).show();
							jQuery( '#mainwp-message-zone' ).fadeOut( 3000 );
						return;
					}, 'json' );
				} );
			} );
		</script>
		<?php
	}

	public static function renameGroup() {
		if ( isset( $_POST[ 'groupId' ] ) && MainWP_Utility::ctype_digit( $_POST[ 'groupId' ] ) ) {
			$group = MainWP_DB::Instance()->getGroupById( $_POST[ 'groupId' ] );
			if ( MainWP_Utility::can_edit_group( $group ) ) {
				$name = $_POST[ 'newName' ];
				if ( $name == '' ) {
					$name = $group->name;
				}

				$name	 = self::checkGroupName( $name, $group->id );
				//update group
				$nr		 = MainWP_DB::Instance()->updateGroup( $group->id, $name );

				//Reload group
				$group = MainWP_DB::Instance()->getGroupById( $group->id );
				die( json_encode( array( 'result' => $group->name ) ) );
			}
		}
	}

	public static function deleteGroup() {
		if ( isset( $_POST[ 'groupId' ] ) && MainWP_Utility::ctype_digit( $_POST[ 'groupId' ] ) ) {
			$group = MainWP_DB::Instance()->getGroupById( $_POST[ 'groupId' ] );
			if ( MainWP_Utility::can_edit_group( $group ) ) {
				//Remove from DB
				$nr = MainWP_DB::Instance()->removegroup( $group->id );

				if ( $nr > 0 ) {
					die( 'OK' );
				}
			}
		}
		die( 'ERROR' );
	}

	public static function checkGroupName( $groupName, $groupId = null ) {
		if ( $groupName == '' ) {
			$groupName = __( 'New group', 'mainwp' );
		}

		$groupName = esc_html( $groupName );

		$cnt = null;
		if ( preg_match( '/(.*) \(\d\)/', $groupName, $matches ) ) {
			$groupName = $matches[ 1 ];
		}

		$group = MainWP_DB::Instance()->getGroupByNameForUser( $groupName );
		while ( $group && ( ( $groupId == null ) || ( $group->id != $groupId ) ) ) {
			if ( $cnt == null ) {
				$cnt = 1;
			} else {
				$cnt ++;
			}

			$group = MainWP_DB::Instance()->getGroupByNameForUser( $groupName . ' (' . $cnt . ')' );
		}

		return $groupName . ( $cnt == null ? '' : ' (' . $cnt . ')' );
	}

	public static function addGroup() {
		global $current_user;
		if ( isset( $_POST[ 'newName' ] ) ) {
			$groupId = MainWP_DB::Instance()->addGroup( $current_user->ID, self::checkGroupName( $_POST[ 'newName' ] ) );
			do_action( 'mainwp_added_new_group', $groupId );
			$group	 = MainWP_DB::Instance()->getGroupById( $groupId );
			self::createGroupItem( $group );
			die();
		}
		die( json_encode( array( 'error' => 1 ) ) );
	}

	public static function getSites() {
		if ( isset( $_POST[ 'groupId' ] ) && MainWP_Utility::ctype_digit( $_POST[ 'groupId' ] ) ) {
			$group = MainWP_DB::Instance()->getGroupById( $_POST[ 'groupId' ] );
			if ( MainWP_Utility::can_edit_group( $group ) ) {
				$websites	 = MainWP_DB::Instance()->getWebsitesByGroupId( $group->id );
				$websiteIds	 = array();
				if ( !empty( $websites ) ) {
					foreach ( $websites as $website ) {
						$websiteIds[] = $website->id;
					}
				}

				return json_encode( $websiteIds );
			}
		}
		die( 'ERROR' );
	}

	public static function updateGroup() {
		if ( isset( $_POST[ 'groupId' ] ) && MainWP_Utility::ctype_digit( $_POST[ 'groupId' ] ) ) {
			$group = MainWP_DB::Instance()->getGroupById( $_POST[ 'groupId' ] );
			if ( MainWP_Utility::can_edit_group( $group ) ) {
				MainWP_DB::Instance()->clearGroup( $group->id );
				if ( isset( $_POST[ 'websiteIds' ] ) ) {
					foreach ( $_POST[ 'websiteIds' ] as $websiteId ) {
						$website = MainWP_DB::Instance()->getWebsiteById( $websiteId );
						if ( MainWP_Utility::can_edit_website( $website ) ) {
							MainWP_DB::Instance()->updateGroupSite( $group->id, $website->id );
						}
					}
				}
				die( json_encode( array( 'result' => true ) ) );
			}
		}

		die( json_encode( array( 'result' => false ) ) );
	}

}
