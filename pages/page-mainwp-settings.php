<?php

class MainWP_Settings {

	public static function getClassName() {
		return __CLASS__;
	}

	public static $subPages;

	public static function init() {
		/**
		 * This hook allows you to render the Settings page header via the 'mainwp-pageheader-settings' action.
		 * @link https://mainwp.com/codex/#mainwp-pageheader-settings
		 *
		 * This hook is normally used in the same context of 'mainwp-getsubpages-settings'
		 * @link https://mainwp.com/codex/#mainwp-getsubpages-settings
		 *
		 * @see \MainWP_Settings::renderHeader
		 */
		add_action( 'mainwp-pageheader-settings', array( MainWP_Settings::getClassName(), 'renderHeader' ) );

		/**
		 * This hook allows you to render the Settings page footer via the 'mainwp-pagefooter-settings' action.
		 * @link https://mainwp.com/codex/#mainwp-pagefooter-settings
		 *
		 * This hook is normally used in the same context of 'mainwp-getsubpages-settings'
		 * @link https://mainwp.com/codex/#mainwp-getsubpages-settings
		 *
		 * @see \MainWP_Settings::renderFooter
		 */
		add_action( 'mainwp-pagefooter-settings', array( MainWP_Settings::getClassName(), 'renderFooter' ) );

		add_action( 'admin_init', array( MainWP_Settings::getClassName(), 'admin_init' ) );

		add_action( 'mainwp_help_sidebar_content', array( MainWP_Settings::getClassName(), 'mainwp_help_content' ) ); // Hook the Help Sidebar content
	}

	public static function admin_init() {
		self::exportSites();
	}

	public static function initMenu() {
		$_page = add_submenu_page( 'mainwp_tab', __( 'Settings Global options', 'mainwp' ), ' <span id="mainwp-Settings">' . __( 'Settings', 'mainwp' ) . '</span>', 'read', 'Settings', array(
			MainWP_Settings::getClassName(),
			'render',
		) );

		if ( !MainWP_Menu::is_disable_menu_item( 3, 'MainWPTools' ) ) {
			$_page = add_submenu_page( 'mainwp_tab', __( 'MainWP Tools', 'mainwp' ), ' <div class="mainwp-hidden">' . __( 'MainWP Tools', 'mainwp' ) . '</div>', 'read', 'MainWPTools', array(
				MainWP_Settings::getClassName(),
				'renderMainWPTools',
			) );
		}

		if ( !MainWP_Menu::is_disable_menu_item( 3, 'SettingsAdvanced' ) ) {
			$_page = add_submenu_page( 'mainwp_tab', __( 'Advanced Options', 'mainwp' ), ' <div class="mainwp-hidden">' . __( 'Advanced Options', 'mainwp' ) . '</div>', 'read', 'SettingsAdvanced', array(
				MainWP_Settings::getClassName(),
				'renderAdvanced',
			) );
		}

        if (get_option( 'mainwp_enable_managed_cr_for_wc' ) == 1 ) {
            if ( !MainWP_Menu::is_disable_menu_item( 3, 'SettingsClientReportsResponder' ) ) {
                $_page = add_submenu_page( 'mainwp_tab', __( 'Managed Client Reports', 'mainwp' ), ' <div class="mainwp-hidden">' . __( 'Managed Client Reports', 'mainwp' ) . '</div>', 'read', 'SettingsClientReportsResponder', array(
                    MainWP_Settings::getClassName(),
                    'renderReportResponder',
                ) );
            }
        }

		/**
		 * This hook allows you to add extra sub pages to the Settings page via the 'mainwp-getsubpages-settings' filter.
		 * @link https://mainwp.com/codex/#mainwp-getsubpages-settings
		 */
		self::$subPages = apply_filters( 'mainwp-getsubpages-settings', array() );
		if ( isset( self::$subPages ) && is_array( self::$subPages ) ) {
			foreach ( self::$subPages as $subPage ) {
				if ( MainWP_Menu::is_disable_menu_item( 3, 'Settings' . $subPage[ 'slug' ] ) ) {
					continue;
				}
				add_submenu_page( 'mainwp_tab', $subPage[ 'title' ], '<div class="mainwp-hidden">' . $subPage[ 'title' ] . '</div>', 'read', 'Settings' . $subPage[ 'slug' ], $subPage[ 'callback' ] );
			}
		}

		MainWP_Settings::init_left_menu( self::$subPages );
	}

	public static function initMenuSubPages() {
		?>
		<div id="menu-mainwp-Settings" class="mainwp-submenu-wrapper">
			<div class="wp-submenu sub-open" style="">
				<div class="mainwp_boxout">
					<div class="mainwp_boxoutin"></div>
					<a href="<?php echo admin_url( 'admin.php?page=Settings' ); ?>" class="mainwp-submenu"><?php _e( 'Global Options', 'mainwp' ); ?></a>
					<?php if ( !MainWP_Menu::is_disable_menu_item( 3, 'SettingsAdvanced' ) ) { ?>
						<a href="<?php echo admin_url( 'admin.php?page=SettingsAdvanced' ); ?>" class="mainwp-submenu"><?php _e( 'Advanced Options', 'mainwp' ); ?></a>
					<?php } ?>
					<?php if ( !MainWP_Menu::is_disable_menu_item( 3, 'MainWPTools' ) ) { ?>
						<a href="<?php echo admin_url( 'admin.php?page=MainWPTools' ); ?>" class="mainwp-submenu"><?php _e( 'MainWP Tools', 'mainwp' ); ?></a>
					<?php } ?>
					<?php
                    if (get_option( 'mainwp_enable_managed_cr_for_wc' ) == 1 ) {
                        if ( !MainWP_Menu::is_disable_menu_item( 3, 'SettingsClientReportsResponder' ) ) { ?>
						<a href="<?php echo admin_url( 'admin.php?page=SettingsClientReportsResponder' ); ?>" class="mainwp-submenu"><?php _e( 'Managed Client Reports', 'mainwp' ); ?></a>
					<?php }
                    }
                    ?>
					<?php
					if ( isset( self::$subPages ) && is_array( self::$subPages ) && ( count( self::$subPages ) > 0 ) ) {
						foreach ( self::$subPages as $subPage ) {
							if ( MainWP_Menu::is_disable_menu_item( 3, 'Settings' . $subPage[ 'slug' ] ) ) {
								continue;
							}
							?>
							<a href="<?php echo admin_url( 'admin.php?page=Settings' . $subPage[ 'slug' ] ); ?>"
							   class="mainwp-submenu"><?php echo esc_html($subPage[ 'title' ]); ?></a>
							   <?php
						   }
					   }
					   ?>
				</div>
			</div>
		</div>
		<?php
	}

	static function init_left_menu( $subPages = array() ) {
		MainWP_Menu::add_left_menu( array(
			'title'		 => __( 'Settings', 'mainwp' ),
			'parent_key' => 'mainwp_tab',
			'slug'		 => 'Settings',
			'href'		 => 'admin.php?page=Settings',
			'icon'		 => '<i class="cogs icon"></i>'
		), 1 ); // level 1


		$init_sub_subleftmenu = array(
			array( 'title'		 => __( 'Global Options', 'mainwp' ),
				'parent_key' => 'Settings',
				'href'		 => 'admin.php?page=Settings',
				'slug'		 => 'Settings',
				'right'		 => ''
			),
			array( 'title'		 => __( 'Advanced Options', 'mainwp' ),
				'parent_key' => 'Settings',
				'href'		 => 'admin.php?page=SettingsAdvanced',
				'slug'		 => 'SettingsAdvanced',
				'right'		 => ''
			),
			array( 'title'		 => __( 'MainWP Tools', 'mainwp' ),
				'parent_key' => 'Settings',
				'href'		 => 'admin.php?page=MainWPTools',
				'slug'		 => 'MainWPTools',
				'right'		 => ''
			)
		);

        if (get_option( 'mainwp_enable_managed_cr_for_wc' ) == 1 ) {
            $init_sub_subleftmenu[] = array( 'title'		 => __( 'Managed Client Reports', 'mainwp' ),
				'parent_key' => 'Settings',
				'href'		 => 'admin.php?page=SettingsClientReportsResponder',
				'slug'		 => 'SettingsClientReportsResponder',
				'right'		 => ''
			);
        }

		MainWP_Menu::init_subpages_left_menu( $subPages, $init_sub_subleftmenu, 'Settings', 'Settings' );
		foreach ( $init_sub_subleftmenu as $item ) {
			if ( MainWP_Menu::is_disable_menu_item( 3, $item[ 'slug' ] ) )
				continue;

			MainWP_Menu::add_left_menu( $item, 2);
		}
	}

	/**
	 * @param string $shownPage The page slug shown at this moment
	 */
	public static function renderHeader( $shownPage = '') {

		$params = array(
			'title' => __( 'MainWP Settings', 'mainwp' )
		);

		MainWP_UI::render_top_header( $params );

		$renderItems = array();

		$renderItems[] = array(
			'title' => __( 'Global Options', 'mainwp' ),
			'href' => "admin.php?page=Settings",
			'active' => ($shownPage == '') ? true : false
		);

		if ( !MainWP_Menu::is_disable_menu_item( 3, 'SettingsAdvanced' ) ) {
			$renderItems[] = array(
				'title' => __( 'Advanced Options', 'mainwp' ),
				'href' => "admin.php?page=SettingsAdvanced",
				'active' => ($shownPage == 'Advanced') ? true : false
			);
		}

		if ( !MainWP_Menu::is_disable_menu_item( 3, 'MainWPTools' ) ) {
			$renderItems[] = array(
				'title' => __( 'MainWP Tools', 'mainwp' ),
				'href' => "admin.php?page=MainWPTools",
				'active' => ($shownPage == 'MainWPTools') ? true : false
			);
		}

        if (get_option( 'mainwp_enable_managed_cr_for_wc' ) == 1 ) {
            if ( !MainWP_Menu::is_disable_menu_item( 3, 'SettingsClientReportsResponder' ) ) {
                $renderItems[] = array(
                    'title' => __( 'Managed Client Reports', 'mainwp' ),
                    'href' => "admin.php?page=SettingsClientReportsResponder",
                    'active' => ($shownPage == 'SettingsClientReportsResponder') ? true : false
                );
            }
        }

		if ( isset( self::$subPages ) && is_array( self::$subPages ) ) {
			foreach ( self::$subPages as $subPage ) {
				if ( MainWP_Menu::is_disable_menu_item( 3, 'Settings' . $subPage[ 'slug' ] ) )
					continue;

				$item = array();
				$item['title'] = $subPage['title'];
				$item['href'] = 'admin.php?page=Settings' . $subPage['slug'];
				$item['active'] = ($subPage[ 'slug' ] == $shownPage) ? true : false;
			}
		}

		MainWP_UI::render_page_navigation( $renderItems );

	}



	/**
	 * @param string $shownPage The page slug shown at this moment
	 */
	public static function renderFooter( $shownPage ) {
		echo "</div>";
	}

	public static function handleSettingsPost() {
		if ( isset( $_POST[ 'submit' ] ) && wp_verify_nonce( $_POST[ 'wp_nonce' ], 'Settings' ) ) {
			$userExtension	 = MainWP_DB::Instance()->getUserExtension();
			$save_emails	 	 = array();
			$user_emails	 	 = $_POST[ 'mainwp_options_email' ];
			if ( is_array( $user_emails ) ) {
				foreach ( $user_emails as $email ) {
					$email = esc_html( trim( $email ) );
					if ( !empty( $email ) && !in_array( $email, $save_emails ) ) {
						$save_emails[] = $email;
					}
				}
			}

			$save_emails				 				 = implode( ',', $save_emails );
			$userExtension->user_email	 = $save_emails;

			$userExtension->heatMap		 	 = ( !isset( $_POST[ 'mainwp_options_footprint_heatmap' ] ) ? 1 : 0 );
			$userExtension->pluginDir	 	 = '';

			MainWP_DB::Instance()->updateUserExtension( $userExtension );
			if ( MainWP_Utility::isAdmin() ) {
				MainWP_Utility::update_option( 'mainwp_optimize', (!isset( $_POST[ 'mainwp_optimize' ] ) ? 0 : 1 ) );
				$val = ( !isset( $_POST[ 'mainwp_pluginAutomaticDailyUpdate' ] ) ? 0 : $_POST[ 'mainwp_pluginAutomaticDailyUpdate' ] );
				MainWP_Utility::update_option( 'mainwp_pluginAutomaticDailyUpdate', $val );
				$val = ( !isset( $_POST[ 'mainwp_themeAutomaticDailyUpdate' ] ) ? 0 : $_POST[ 'mainwp_themeAutomaticDailyUpdate' ] );
				MainWP_Utility::update_option( 'mainwp_themeAutomaticDailyUpdate', $val );
				$val = ( !isset( $_POST[ 'mainwp_automaticDailyUpdate' ] ) ? 0 : $_POST[ 'mainwp_automaticDailyUpdate' ] );
				MainWP_Utility::update_option( 'mainwp_automaticDailyUpdate', $val );
				$val = ( !isset( $_POST[ 'mainwp_show_language_updates' ] ) ? 0 : 1 );
				MainWP_Utility::update_option( 'mainwp_show_language_updates', $val );				
				$val = ( !isset( $_POST[ 'mainwp_disable_update_confirmations' ] ) ? 0 : intval( $_POST[ 'mainwp_disable_update_confirmations' ] ) );
				MainWP_Utility::update_option( 'mainwp_disable_update_confirmations', $val );				
				$val = ( !isset( $_POST[ 'mainwp_backup_before_upgrade' ] ) ? 0 : 1 );
				MainWP_Utility::update_option( 'mainwp_backup_before_upgrade', $val );
				$val = ( !isset( $_POST[ 'mainwp_backup_before_upgrade_days' ] ) ? 7 : $_POST[ 'mainwp_backup_before_upgrade_days' ] );
				MainWP_Utility::update_option( 'mainwp_backup_before_upgrade_days', $val );								
				

				if ( MainWP_Extensions::isExtensionAvailable( 'mainwp-comments-extension' ) ) {
					MainWP_Utility::update_option( 'mainwp_maximumComments', isset( $_POST[ 'mainwp_maximumComments' ] ) ? intval( $_POST[ 'mainwp_maximumComments' ] ) : 50  );
				}
				MainWP_Utility::update_option( 'mainwp_wp_cron', (!isset( $_POST[ 'mainwp_options_wp_cron' ] ) ? 0 : 1 ) );
				MainWP_Utility::update_option( 'mainwp_timeDailyUpdate', $_POST[ 'mainwp_timeDailyUpdate' ] );
				MainWP_Utility::update_option( 'mainwp_frequencyDailyUpdate', intval( $_POST[ 'mainwp_frequencyDailyUpdate' ] ) );
				
                $val = ( isset( $_POST[ 'mainwp_sidebarPosition' ] ) ? intval($_POST[ 'mainwp_sidebarPosition' ]) : 1 );
                if ( $user = wp_get_current_user() ) {
                    update_user_option($user->ID, "mainwp_sidebarPosition", $val, true);
                }

				MainWP_Utility::update_option( 'mainwp_numberdays_Outdate_Plugin_Theme', MainWP_Utility::ctype_digit( $_POST[ 'mainwp_numberdays_Outdate_Plugin_Theme' ] ) ? intval( $_POST[ 'mainwp_numberdays_Outdate_Plugin_Theme' ] ) : 365  );
				$ignore_http = isset( $_POST[ 'mainwp_ignore_http_response_status' ] ) ? $_POST[ 'mainwp_ignore_http_response_status' ] : '';
				MainWP_Utility::update_option( 'mainwp_ignore_HTTP_response_status', $ignore_http );

				$check_http_response = ( isset( $_POST[ 'mainwp_check_http_response' ] ) ? 1 : 0 );
				MainWP_Utility::update_option( 'mainwp_check_http_response', $check_http_response );
			}

			return true;
		}

		return false;
	}

	public static function render() {
		if ( !mainwp_current_user_can( 'dashboard', 'manage_dashboard_settings' ) ) {
			mainwp_do_not_have_permissions( __( 'manage dashboard settings', 'mainwp' ) );
			return;
		}
		
		
		self::renderHeader( '' );
		?>
		<div id="mainwp-general-settings" class="ui segment">
				<?php if ( isset( $_GET['message'] ) && $_GET['message'] = 'saved' ) : ?>
					<div class="ui green message"><i class="close icon"></i><?php _e( 'Settings have been saved successfully!', 'mainwp' ); ?></div>
				<?php endif; ?>
				<div class="ui form">
					<form method="POST" action="admin.php?page=Settings" id="mainwp-settings-page-form">
					  <input type="hidden" name="wp_nonce" value="<?php echo wp_create_nonce( 'Settings' ); ?>" />
						<h3 class="ui dividing header"><?php esc_html_e( 'Optimization', 'mainwp' ); ?></h3>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Optimize for shared hosting or big networks', 'mainwp' ); ?></label>
						  <div class="ten wide column ui toggle checkbox"  data-tooltip="<?php esc_attr_e( 'If enabled, your MainWP Dashboard will cache updates for faster loading.', 'mainwp' ); ?>" data-inverted="" data-position="bottom left">
								<input type="checkbox" name="mainwp_optimize" id="mainwp_optimize" <?php echo ( ( get_option( 'mainwp_optimize' ) == 1 ) ? 'checked="true"' : ''); ?> />
							</div>
						</div>
						<h3 class="ui dividing header"><?php esc_html_e( 'General Settings', 'mainwp' ); ?></h3>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Notification email(s)', 'mainwp' ); ?></label>
						  <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Enter your email address(es) to receive email notifications from your MainWP Dashboard.', 'mainwp' ); ?>" data-inverted="" data-position="top left" >
								<div class="mainwp-multi-emails">
									<?php
									$user_emails = MainWP_Utility::getNotificationEmail();
								  $user_emails = explode( ',', $user_emails );
								  $i			 	   = 0;
				          foreach ( $user_emails as $email ) {
				            $i++;
				            ?>
										<div class="ui action input">
											<input type="text" class="" id="mainwp_options_email" name="mainwp_options_email[<?php echo $i; ?>]" value="<?php echo $email; ?>"/>
											<a href="#" class="ui button basic red mainwp-multi-emails-remove" data-tooltip="<?php esc_attr_e( 'Remove this email address', 'mainwp' ); ?>" data-inverted=""><?php _e( 'Delete', 'mainwp' ); ?></a>
										</div>
										<div class="ui hidden fitted divider"></div>
									<?php } ?>
									<a href="#" id="mainwp-multi-emails-add" class="ui button basic green" data-tooltip="<?php esc_attr_e( 'Add another email address to receive email notifications to multiple email addresses.', 'mainwp' ); ?>" data-inverted=""><?php _e( 'Add Another Email', 'mainwp' ); ?></a>
								</div>
							</div>
						</div>
						<script type="text/javascript">
							jQuery( document ).ready( function () {
								jQuery( '#mainwp-multi-emails-add' ).on( 'click', function () {
										jQuery( '#mainwp-multi-emails-add' ).before( '<div class="ui action input"><input type="text" name="mainwp_options_email[]" value=""/><a href="#" class="ui button basic red mainwp-multi-emails-remove" data-tooltip="Remove this email address" data-inverted=""><?php _e( 'Delete', 'mainwp' ); ?></a></div><div class="ui hidden fitted divider"></div>' );
										return false;
								} );
								jQuery( '.mainwp-multi-emails-remove' ).on( 'click', function () {
										jQuery( this ).closest( '.ui.action.input' ).remove();
										return false;
								} );
							} );
					  </script>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Enable WP Cron', 'mainwp' ); ?></label>
						  <div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'Disabling this option will disable the WP Cron so all scheduled events will stop working.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="checkbox" name="mainwp_options_wp_cron" id="mainwp_options_wp_cron" <?php echo( ( get_option( 'mainwp_wp_cron' ) == 1 ) || ( get_option( 'mainwp_wp_cron' ) === false ) ? 'checked="true"' : '' ); ?>/>
							</div>
						</div>					
					<?php
					$timeDailyUpdate	= get_option( 'mainwp_timeDailyUpdate' );
					$frequencyDailyUpdate = get_option( 'mainwp_frequencyDailyUpdate' );					
					?>					  
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php esc_html_e( 'Automatic daily sync time', 'mainwp' ); ?></label>
						<div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Set specific time for the automatic daily sync process.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
							<div class="time-selector">
								<div class="ui input left icon">
									<i class="clock icon"></i>
									<input type="text" name="mainwp_timeDailyUpdate" id="mainwp_timeDailyUpdate" value="<?php echo esc_html( $timeDailyUpdate ); ?>" />
								</div>
							</div>
							<script type="text/javascript">
							jQuery( document ).ready( function() {
									jQuery( '.time-selector' ).calendar( {
										type: 'time',
										ampm: false
									} );
							} );
							</script>
						</div>
					</div>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php esc_html_e( 'Daily Update frequency', 'mainwp' ); ?></label>
						<div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Daily Update frequency', 'mainwp' ); ?>" data-inverted="" data-position="top left">
							<select name="mainwp_frequencyDailyUpdate" id="mainwp_frequencyDailyUpdate" class="ui dropdown">
								<option value="1" <?php if ( $frequencyDailyUpdate == 1 ) { ?>selected<?php } ?>><?php _e( 'Once per day', 'mainwp' ); ?></option>
								<option value="2" <?php if ( $frequencyDailyUpdate == 2 ) { ?>selected<?php } ?>><?php _e( 'Twice per day', 'mainwp' ); ?></option>
								<option value="3" <?php if ( $frequencyDailyUpdate == 3 ) { ?>selected<?php } ?>><?php _e( 'Three times per day', 'mainwp' ); ?></option>
								<option value="4" <?php if ( $frequencyDailyUpdate == 4 ) { ?>selected<?php } ?>><?php _e( 'Four times per day', 'mainwp' ); ?></option>
								<option value="5" <?php if ( $frequencyDailyUpdate == 5 ) { ?>selected<?php } ?>><?php _e( 'Five times per day', 'mainwp' ); ?></option>
								<option value="6" <?php if ( $frequencyDailyUpdate == 6 ) { ?>selected<?php } ?>><?php _e( 'Six times per day', 'mainwp' ); ?></option>
								<option value="7" <?php if ( $frequencyDailyUpdate == 7 ) { ?>selected<?php } ?>><?php _e( 'Seven times per day', 'mainwp' ); ?></option>
								<option value="8" <?php if ( $frequencyDailyUpdate == 8 ) { ?>selected<?php } ?>><?php _e( 'Eight times per day', 'mainwp' ); ?></option>
								<option value="9" <?php if ( $frequencyDailyUpdate == 9 ) { ?>selected<?php } ?>><?php _e( 'Nine times per day', 'mainwp' ); ?></option>
								<option value="10" <?php if ( $frequencyDailyUpdate == 10 ) { ?>selected<?php } ?>><?php _e( 'Ten times per day', 'mainwp' ); ?></option>
								<option value="11" <?php if ( $frequencyDailyUpdate == 11 ) { ?>selected<?php } ?>><?php _e( 'Eleven times per day', 'mainwp' ); ?></option>
								<option value="12" <?php if ( $frequencyDailyUpdate == 12 ) { ?>selected<?php } ?>><?php _e( 'Twelve times per day', 'mainwp' ); ?></option>
							</select>
						</div>
					</div>
					  
            <?php

            $sidebarPosition = get_user_option("mainwp_sidebarPosition");
            if ( false  === $sidebarPosition )
              $sidebarPosition = 1;

            ?>
            <div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Sidebar position', 'mainwp' ); ?></label>
						  <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Select if you want to show sidebar with option on left or right.', 'mainwp' ); ?>" data-inverted="" data-position="bottom left">
								<select name="mainwp_sidebarPosition" id="mainwp_sidebarPosition" class="ui dropdown">
									<option value="1" <?php if ( $sidebarPosition == 1 ) { ?>selected<?php } ?>><?php _e( 'Right (default)', 'mainwp' ); ?></option>
									<option value="0" <?php if ( $sidebarPosition == 0 ) { ?>selected<?php } ?>><?php _e( 'Left', 'mainwp' ); ?></option>
								</select>
							</div>
						</div>

						<h3 class="ui dividing header"><?php esc_html_e( 'Updates Settings', 'mainwp' ); ?></h3>
						<?php
						$snAutomaticDailyUpdate				 			 	 = get_option( 'mainwp_automaticDailyUpdate' );
						$snPluginAutomaticDailyUpdate					 = get_option( 'mainwp_pluginAutomaticDailyUpdate' );
						$snThemeAutomaticDailyUpdate					 = get_option( 'mainwp_themeAutomaticDailyUpdate' );
					  $backup_before_upgrade				 			 	 = get_option( 'mainwp_backup_before_upgrade' );
					  $mainwp_backup_before_upgrade_days	 	 = get_option( 'mainwp_backup_before_upgrade_days' )
						;
					  if ( empty( $mainwp_backup_before_upgrade_days ) || !ctype_digit( $mainwp_backup_before_upgrade_days ) )
                        $mainwp_backup_before_upgrade_days	 = 7;

					  $mainwp_show_language_updates		 			 = get_option( 'mainwp_show_language_updates', 1 );

					  $update_time		 											 = MainWP_Utility::getWebsitesAutomaticUpdateTime();
					  $lastAutomaticUpdate 									 = $update_time[ 'last' ];
					  $nextAutomaticUpdate 									 = $update_time[ 'next' ];

					  $enableLegacyBackupFeature	 					 = get_option( 'mainwp_enableLegacyBackupFeature' );
					  $primaryBackup				 								 = get_option( 'mainwp_primaryBackup' );					  
					  $disableUpdateConfirmations				 			 	 = get_option( 'mainwp_disable_update_confirmations', 0);

						$http_error_codes = array(
							'200'	 => 'OK',
							'201'	 => 'Created',
							'202'	 => 'Accepted',
							'203'	 => 'Non-Authoritative Information',
							'204'	 => 'No Content',
							'205'	 => 'Reset Content',
							'206'	 => 'Partial Content',
							'300'	 => 'Multiple Choices',
							'301'	 => 'Moved Permanently',
							'302'	 => 'Found',
							'303'	 => 'See Other',
							'304'	 => 'Not Modified',
							'305'	 => 'Use Proxy',
							'306'	 => '(Unused)',
							'307'	 => 'Temporary Redirect',
							'400'	 => 'Bad Request',
							'401'	 => 'Unauthorized',
							'402'	 => 'Payment Required',
							'403'	 => 'Forbidden',
							'404'	 => 'Not Found',
							'405'	 => 'Method Not Allowed',
							'406'	 => 'Not Acceptable',
							'407'	 => 'Proxy Authentication Required',
							'408'	 => 'Request Timeout',
							'409'	 => 'Conflict',
							'410'	 => 'Gone',
							'411'	 => 'Length Required',
							'412'	 => 'Precondition Failed',
							'413'	 => 'Request Entity Too Large',
							'414'	 => 'Request-URI Too Long',
							'415'	 => 'Unsupported Media Type',
							'416'	 => 'Requested Range Not Satisfiable',
							'417'	 => 'Expectation Failed',
							'500'	 => 'Internal Server Error',
							'501'	 => 'Not Implemented',
							'502'	 => 'Bad Gateway',
							'503'	 => 'Service Unavailable',
							'504'	 => 'Gateway Timeout',
							'505'	 => 'HTTP Version Not Supported' );
						?>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Plugin automatic updates', 'mainwp' ); ?></label>
						  <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Enable or disable automatic plugins updates. If enabled, MainWP will update only plugins that you have marked as Trusted.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<select name="mainwp_pluginAutomaticDailyUpdate" id="mainwp_pluginAutomaticDailyUpdate" class="ui dropdown">
									<option value="1" <?php if ( $snPluginAutomaticDailyUpdate == 1 ) { ?>selected<?php } ?>><?php _e( 'Install Trusted Updates', 'mainwp' ); ?></option>
									<option value="0" <?php if ( ( $snPluginAutomaticDailyUpdate !== false && $snPluginAutomaticDailyUpdate == 0 ) ||  $snPluginAutomaticDailyUpdate == 2 ) { ?>selected<?php } ?>><?php _e( 'Disabled', 'mainwp' ); ?></option>
								</select>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Theme automatic updates', 'mainwp' ); ?></label>
						  <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Enable or disable automatic themes updates. If enabled, MainWP will update only themes that you have marked as Trusted.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<select name="mainwp_themeAutomaticDailyUpdate" id="mainwp_themeAutomaticDailyUpdate" class="ui dropdown">
									<option value="1" <?php if ( $snThemeAutomaticDailyUpdate == 1 ) { ?>selected<?php } ?>><?php _e( 'Install Trusted Updates', 'mainwp' ); ?></option>
									<option value="0" <?php if ( ($snThemeAutomaticDailyUpdate !== false && $snThemeAutomaticDailyUpdate == 0) || $snThemeAutomaticDailyUpdate == 2 ) { ?>selected<?php } ?>><?php _e( 'Disabled', 'mainwp' ); ?></option>
								</select>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'WP Core automatic updates. If enabled, MainWP will update only Trusted sites.', 'mainwp' ); ?></label>
							<div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Enable or disable automatic WordPress core updates.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<select name="mainwp_automaticDailyUpdate" id="mainwp_automaticDailyUpdate" class="ui dropdown">
									<option value="1" <?php if ( $snAutomaticDailyUpdate == 1 ) { ?>selected<?php } ?>><?php _e( 'Install Trusted Updates', 'mainwp' ); ?></option>
									<option value="0" <?php if ( ( $snAutomaticDailyUpdate !== false && $snAutomaticDailyUpdate == 0 ) || $snAutomaticDailyUpdate == 2 ) { ?>selected<?php } ?>><?php _e( 'Disabled', 'mainwp' ); ?></option>
								</select>
								<div class="ui hidden divider"></div>
								<div class="ui label"><?php _e( 'Last run: ', 'mainwp' ); ?><?php echo esc_html($lastAutomaticUpdate); ?></div>
								<div class="ui label"><?php _e( 'Next run: ', 'mainwp' ); ?><?php echo esc_html($nextAutomaticUpdate); ?></div>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Show WordPress language updates', 'mainwp' ); ?></label>
						  <div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'Enable if you want to manage Translation updates', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="checkbox" name="mainwp_show_language_updates" id="mainwp_show_language_updates" <?php echo( $mainwp_show_language_updates == 1 ? 'checked="true"' : '' ); ?>/>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Update confirmations', 'mainwp' ); ?></label>
							<div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'Choose if you want to disable the popup confirmations when performing updates.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<select name="mainwp_disable_update_confirmations" id="mainwp_disable_update_confirmations" class="ui dropdown">
									<option value="0" <?php if ( $disableUpdateConfirmations == 0 ) { ?>selected<?php } ?>><?php _e( "Enable", 'mainwp' ); ?></option>
									<option value="2" <?php if ( $disableUpdateConfirmations == 2 ) { ?>selected<?php } ?>><?php _e( 'Disable', 'mainwp' ); ?></option>
									<option value="1" <?php if ( $disableUpdateConfirmations == 1 ) { ?>selected<?php } ?>><?php _e( 'Disable for single updates', 'mainwp' ); ?></option>
								</select>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Check site HTTP response after update', 'mainwp' ); ?></label>
						  <div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'Enable if you want your MainWP Dashboard to check child site header response after updates.', 'mainwp' ); ?>" data-inverted="" data-position="bottom left">
								<input type="checkbox" name="mainwp_check_http_response" id="mainwp_check_http_response" <?php echo( ( get_option( 'mainwp_check_http_response', 0 ) == 1 ) ? 'checked="true"' : '' ); ?>/>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Ignored HTTP response statuses', 'mainwp' ); ?></label>
						  <div class="ten wide column"  data-tooltip="<?php esc_attr_e( 'Select response codes that you want your MainWP Dashboard to ignore.', 'mainwp' ); ?>" data-inverted="" data-position="bottom left">
                <div class="ui multiple selection dropdown" init-value="<?php echo( get_option( 'mainwp_ignore_HTTP_response_status', '' ) ); ?>">
                  <input name="mainwp_ignore_http_response_status" type="hidden">
                  <i class="dropdown icon"></i>
                  <div class="default text"></div>
                  <div class="menu">
                    <?php
                    foreach ($http_error_codes as $error_code => $label) {
                      ?>
                      <div class="item" data-value="<?php echo $error_code; ?>"><?php echo $error_code . ' (' . $label . ')'; ?></div>
                      <?php
                    }
                    ?>
                  </div>
                </div>
							</div>
						</div>
						<?php if ( ( ( $enableLegacyBackupFeature && empty( $primaryBackup ) ) || ( empty( $enableLegacyBackupFeature ) && !empty( $primaryBackup ) ) ) ) {  ?>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Require a backup before an update', 'mainwp' ); ?></label>
						  <div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'If enabled, your MainWP Dashboard will check if full backups exists before updating.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="checkbox" name="mainwp_backup_before_upgrade" id="mainwp_backup_before_upgrade" <?php echo( $backup_before_upgrade == 1 ? 'checked="true"' : '' ); ?>/>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Days without of a full backup tolerance', 'mainwp' ); ?></label>
						  <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Set the number of days without of backup tolerance.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="text" name="mainwp_backup_before_upgrade_days" id="mainwp_backup_before_upgrade_days" value="<?php echo esc_attr($mainwp_backup_before_upgrade_days); ?>" />
							</div>
						</div>
						<?php } ?>

						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Abandoned plugins/themes tolerance', 'mainwp' ); ?></label>
						  <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Set how many days without an update before pulgin or theme will be considered as abandoned.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="text" name="mainwp_numberdays_Outdate_Plugin_Theme" id="mainwp_numberdays_Outdate_Plugin_Theme" value="<?php echo( ( get_option( 'mainwp_numberdays_Outdate_Plugin_Theme' ) === false ) ? 365 : get_option( 'mainwp_numberdays_Outdate_Plugin_Theme' ) ); ?>"/>
							</div>
						</div>									
                    <?php MainWP_Manage_Sites_View::renderSettings(); ?>
						<div class="ui divider"></div>
						<input type="submit" name="submit" id="submit" class="ui button green big right floated" value="<?php esc_attr_e( 'Save Settings', 'mainwp' ); ?>"/>
						<div style="clear:both"></div>
					</form>
				</div>
			</div>
		<?php
		self::renderFooter( '' );
	}

	public static function showOpensslLibConfig() {
		if ( MainWP_Server_Information::isOpensslConfigWarning() ) {
			return true;
		} else {
			if ( self::isLocalWindowConfig() ) {
				return false;
			} else {
				// if it is not Openssl error but the setting is not empty the still show setting box to change
				return get_option( 'mainwp_opensslLibLocation' ) != '' ? true : false;
			}
		}
	}

	public static function isLocalWindowConfig() {
		$setup_hosting_type	 = get_option( 'mwp_setup_installationHostingType' );
		$setup_system_type	 = get_option( 'mwp_setup_installationSystemType' );
		if ( $setup_hosting_type == 2 && $setup_system_type == 3 ) {
			return true;
		}
		return false;
	}

	public static function renderAdvanced() {
		if ( ! mainwp_current_user_can( 'dashboard', 'manage_dashboard_settings' ) ) {
			mainwp_do_not_have_permissions( __( 'manage dashboard settings', 'mainwp' ) );
			return;
		}

		if ( isset( $_POST[ 'submit' ] ) && wp_verify_nonce( $_POST[ 'wp_nonce' ], 'SettingsAdvanced' ) ) {
			MainWP_Utility::update_option( 'mainwp_maximumRequests', MainWP_Utility::ctype_digit( $_POST[ 'mainwp_maximumRequests' ] ) ? intval( $_POST[ 'mainwp_maximumRequests' ] ) : 4  );
			MainWP_Utility::update_option( 'mainwp_minimumDelay', MainWP_Utility::ctype_digit( $_POST[ 'mainwp_minimumDelay' ] ) ? intval( $_POST[ 'mainwp_minimumDelay' ] ) : 200  );
			MainWP_Utility::update_option( 'mainwp_maximumIPRequests', MainWP_Utility::ctype_digit( $_POST[ 'mainwp_maximumIPRequests' ] ) ? intval( $_POST[ 'mainwp_maximumIPRequests' ] ) : 1  );
			MainWP_Utility::update_option( 'mainwp_minimumIPDelay', MainWP_Utility::ctype_digit( $_POST[ 'mainwp_minimumIPDelay' ] ) ? intval( $_POST[ 'mainwp_minimumIPDelay' ] ) : 1000  );
			MainWP_Utility::update_option( 'mainwp_maximumSyncRequests', MainWP_Utility::ctype_digit( $_POST[ 'mainwp_maximumSyncRequests' ] ) ? intval( $_POST[ 'mainwp_maximumSyncRequests' ] ) : 8  );
			MainWP_Utility::update_option( 'mainwp_maximumInstallUpdateRequests', MainWP_Utility::ctype_digit( $_POST[ 'mainwp_maximumInstallUpdateRequests' ] ) ? intval( $_POST[ 'mainwp_maximumInstallUpdateRequests' ] ) : 3  );
			MainWP_Utility::update_option( 'mainwp_sslVerifyCertificate', isset( $_POST[ 'mainwp_sslVerifyCertificate' ] ) ? 1 : 0  );
			MainWP_Utility::update_option( 'mainwp_forceUseIPv4', isset( $_POST[ 'mainwp_forceUseIPv4' ] ) ? 1 : 0  );

			if ( isset( $_POST[ 'mainwp_openssl_lib_location' ] ) ) {
                $openssl_loc = trim($_POST['mainwp_openssl_lib_location']);
				if ( self::isLocalWindowConfig() ) {
					MainWP_Utility::update_option( 'mwp_setup_opensslLibLocation', stripslashes( $openssl_loc ) );
				} else {
					MainWP_Utility::update_option( 'mainwp_opensslLibLocation', stripslashes( $openssl_loc ) );
				}
			}
		}
		self::renderHeader( 'Advanced' );
		?>

		<div id="mainwp-advanced-settings" class="ui segment">
            <?php if ( isset( $_POST[ 'submit' ] ) && wp_verify_nonce( $_POST[ 'wp_nonce' ], 'SettingsAdvanced' ) ) : ?>
                <div class="ui green message"><i class="close icon"></i><?php _e( 'Settings have been saved successfully!', 'mainwp' ); ?></div>
				<?php endif; ?>
				<div class="ui form">
					<form method="POST" action="">
					  <input type="hidden" name="wp_nonce" value="<?php echo wp_create_nonce( 'SettingsAdvanced' ); ?>" />
						<?php
						if ( self::showOpensslLibConfig() ) {
							if ( self::isLocalWindowConfig() ) {
								$openssl_loc = get_option( 'mwp_setup_opensslLibLocation', 'c:\xampplite\apache\conf\openssl.cnf' );
							} else {
								//$openssl_loc = get_option( 'mainwp_opensslLibLocation', 'c:\xampplite\apache\conf\openssl.cnf' );
								$openssl_loc = 'c:\xampplite\apache\conf\openssl.cnf';
							}
							?>
							<div class="ui attached message">
								<div class="header"><?php esc_html_e( 'OpenSSL Settings', 'mainwp' ); ?></div>
								<p><?php _e( 'Due to bug with PHP on Windows servers it is required to set the OpenSSL Library location so MainWP Dashboard can connect to your child sites.', 'mainwp' ); ?></p>
								<p><?php echo __( 'If your <strong>openssl.cnf</strong> file is saved to a different path from what is entered please enter your exact path.', 'mainwp' ); ?></p>
							</div>
							<div class="ui attached segment" style="border: 1px solid #dadada;">
								<div class="ui grid field">
									<label class="six wide column middle aligned"><?php esc_html_e( 'OpenSSL.cnf location', 'mainwp' ); ?></label>
								  <div class="ten wide column ui field">
										<input type="text" name="mainwp_openssl_lib_location" value="<?php echo esc_html( $openssl_loc ); ?>">
									</div>
								</div>
							</div>
							<div class="ui attached info message">
								<p><?php echo sprintf( __( 'If you are not sure how to find the openssl.cnf location, please %scheck this help document%s.', 'mainwp' ), '<a href="https://mainwp.com/help/docs/how-to-find-the-openssl-cnf-file/" target="_blank">', '</a>' ); ?></p>
							</div>
							<?php
						}
						?>
						<h3 class="ui dividing header"><?php esc_html_e( 'Cross IP Settings', 'mainwp' ); ?></h3>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Maximum simultaneous requests', 'mainwp' ); ?></label>
						  <div class="ten wide column ui right labeled input" data-tooltip="<?php esc_attr_e( 'If too many requests are sent out, they will begin to time out. This causes your sites to be shown as offline while they are up and running.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="text" name="mainwp_maximumRequests" id="mainwp_maximumRequests" value="<?php echo( ( get_option( 'mainwp_maximumRequests' ) === false ) ? 4 : get_option( 'mainwp_maximumRequests' ) ); ?>"/><div class="ui basic label"><?php _e( 'Default: 4', 'mainwp' ); ?></div>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Minimum delay between requests', 'mainwp' ); ?></label>
						  <div class="ten wide column ui right labeled input" data-tooltip="<?php esc_attr_e( 'This option allows you to control minimum time delay between two requests.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="text" name="mainwp_minimumDelay" id="mainwp_minimumDelay" value="<?php echo( ( get_option( 'mainwp_minimumDelay' ) === false ) ? 200 : get_option( 'mainwp_minimumDelay' ) ); ?>"/><div class="ui basic label"><?php _e( 'Default: 200', 'mainwp' ); ?></div>
							</div>
						</div>
						<h3 class="ui dividing header"><?php esc_html_e( 'Per IP Settings', 'mainwp' ); ?></h3>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Maximum simultaneous requests per IP', 'mainwp' ); ?></label>
						  <div class="ten wide column ui right labeled input"  data-tooltip="<?php esc_attr_e( 'If too many requests are sent out, they will begin to time out. This causes your sites to be shown as offline while they are up and running.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="text" name="mainwp_maximumIPRequests" id="mainwp_maximumIPRequests" value="<?php echo( ( get_option( 'mainwp_maximumIPRequests' ) === false ) ? 1 : get_option( 'mainwp_maximumIPRequests' ) ); ?>"/><div class="ui basic label"><?php _e( 'Default: 1', 'mainwp' ); ?></div>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Minimum delay between requests to the same IP', 'mainwp' ); ?></label>
						  <div class="ten wide column ui right labeled input" data-tooltip="<?php esc_attr_e( 'This option allows you to control minimum time delay between two requests.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="text" name="mainwp_minimumIPDelay" id="mainwp_minimumIPDelay" value="<?php echo( ( get_option( 'mainwp_minimumIPDelay' ) === false ) ? 1000 : get_option( 'mainwp_minimumIPDelay' ) ); ?>"/><div class="ui basic label"><?php _e( 'Default: 1000', 'mainwp' ); ?></div>
							</div>
						</div>
						<h3 class="ui dividing header"><?php esc_html_e( 'Frontend Request Settings', 'mainwp' ); ?></h3>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Maximum simultaneous sync requests', 'mainwp' ); ?></label>
						  <div class="ten wide column ui right labeled input" data-tooltip="<?php esc_attr_e( 'This option allows you to control how many sites your MainWP Dashboard should sync at once.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="text" name="mainwp_maximumSyncRequests" id="mainwp_maximumSyncRequests" value="<?php echo( ( get_option( 'mainwp_maximumSyncRequests' ) === false ) ? 8 : get_option( 'mainwp_maximumSyncRequests' ) ); ?>"/><div class="ui basic label"><?php _e( 'Default: 8', 'mainwp' ); ?></div>
							</div>
						</div>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Maximum simultaneous install and update requests', 'mainwp' ); ?></label>
						  <div class="ten wide column ui right labeled input"  data-tooltip="<?php esc_attr_e( 'This option allows you to control how many update and install requests your MainWP Dashboard should process at once.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="text" name="mainwp_maximumInstallUpdateRequests" id="mainwp_maximumInstallUpdateRequests" value="<?php echo( ( get_option( 'mainwp_maximumInstallUpdateRequests' ) === false ) ? 3 : get_option( 'mainwp_maximumInstallUpdateRequests' ) ); ?>"/><div class="ui basic label"><?php _e( 'Default: 3', 'mainwp' ); ?></div>
							</div>
						</div>
						<h3 class="ui dividing header"><?php esc_html_e( 'SSL Settings', 'mainwp' ); ?></h3>
						<div class="ui grid field" >
							<label class="six wide column middle aligned"><?php esc_html_e( 'Verify SSL certificate', 'mainwp' ); ?></label>
						  <div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'If enabled, your MainWP Dashboard will verify the SSL Certificate on your Child Site (if exists) while connecting the Child Site.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="checkbox" name="mainwp_sslVerifyCertificate" id="mainwp_sslVerifyCertificate" value="checked" <?php echo ( ( get_option( 'mainwp_sslVerifyCertificate' ) === false ) || ( get_option( 'mainwp_sslVerifyCertificate' ) == 1 ) ) ? 'checked="checked"' : ''; ?>/><label><?php esc_html_e( 'Default: On', 'mainwp' ); ?></label>
							</div>
						</div>
						<h3 class="ui dividing header"><?php esc_html_e( 'IPv4 Settings', 'mainwp' ); ?></h3>
						<div class="ui grid field">
							<label class="six wide column middle aligned"><?php esc_html_e( 'Force IPv4', 'mainwp' ); ?></label>
						  <div class="ten wide column ui toggle checkbox"  data-tooltip="<?php esc_attr_e( 'Enable if you want to force your MainWP Dashboard to use IPv4 while tryig to connect child sites.', 'mainwp' ); ?>" data-inverted="" data-position="bottom left">
								<input type="checkbox" name="mainwp_forceUseIPv4" id="mainwp_forceUseIPv4" value="checked" <?php echo ( get_option( 'mainwp_forceUseIPv4' ) == 1 ) ? 'checked="checked"' : ''; ?>/><label><?php esc_html_e( 'Default: No', 'mainwp' ); ?></label>
							</div>
						</div>
						<div class="ui divider"></div>
						<input type="submit" name="submit" id="submit" class="ui green big button right floated" value="<?php esc_attr_e( 'Save Settings', 'mainwp' ); ?>"/>
						<div style="clear:both"></div>
					</form>
				</div>
			</div>
		<?php
		self::renderFooter( 'Advanced' );
	}

	public static function renderMainWPTools() {
		if ( !mainwp_current_user_can( 'dashboard', 'manage_dashboard_settings' ) ) {
			mainwp_do_not_have_permissions( __( 'manage dashboard settings', 'mainwp' ) );

			return;
		}

		self::renderHeader( 'MainWPTools' );

		?>
		<div id="mainwp-tools-settings" class="ui segment">
				<?php if ( isset( $_POST[ 'submit' ] ) && wp_verify_nonce( $_POST[ 'wp_nonce' ], 'MainWPTools' ) ) : ?>
					<div class="ui green message"><i class="close icon"></i><?php _e( 'Settings have been saved successfully!', 'mainwp' ); ?></div>
				<?php endif; ?>
				<div class="ui form">
					<form method="POST" action="">
				  <input type="hidden" name="wp_nonce" value="<?php echo wp_create_nonce( 'MainWPTools' ); ?>" />
					<h3 class="ui dividing header"><?php esc_html_e( 'MainWP Dashboard Tools', 'mainwp' ); ?></h3>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Force your MainWP Dashboard to establish a new connection', 'mainwp' ); ?></label>
					  <div class="ten wide column"  data-tooltip="<?php esc_attr_e( 'Force your MainWP Dashboard to reconnect with your child sites. Only needed if suggested by MainWP Support.', 'mainwp' ); ?>" data-inverted="" data-position="top left"><input type="button" name="" id="force-destroy-sessions-button" class="ui green basic button" value="<?php esc_attr_e( 'Re-establish Connections', 'mainwp' ); ?>" data-tooltip="<?php esc_attr_e( 'Forces your dashboard to reconnect with your child sites. This feature will log out any currently logged in users on the Child sites and require them to re-log in. Only needed if suggested by MainWP Support.', 'mainwp' ); ?>" data-inverted=""/></div>
					</div>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Start the MainWP Quick Setup Wizard', 'mainwp' ); ?></label>
                        <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Click this button to start the Quick Setup Wizard', 'mainwp' ); ?>" data-inverted="" data-position="top left"><a href="admin.php?page=mainwp-setup" class="ui green button basic" ><?php _e( 'Start Quick Setup Wizard', 'mainwp' ); ?></a></div>
					</div>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Export child sites to CSV file', 'mainwp' ); ?></label>
                        <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Click this button to export all connected sites to a CSV file.', 'mainwp' ); ?>" data-inverted="" data-position="top left"><a href="admin.php?page=MainWPTools&doExportSites=yes&_wpnonce=<?php echo wp_create_nonce('export_sites'); ?>" class="ui button green basic"><?php _e( 'Export Child Sites', 'mainwp' ); ?></a></div>
					</div>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Import child sites', 'mainwp' ); ?></label>
                        <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Click this button to import websites to your MainWP Dashboard.', 'mainwp' ); ?>" data-inverted="" data-position="top left"><a href="admin.php?page=managesites&do=bulknew" class="ui button green basic"><?php _e( 'Import Child Sites', 'mainwp' ); ?></a></div>
					</div>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Disconnect all child sites', 'mainwp' ); ?></label>
                       <div class="ten wide column" data-tooltip="<?php esc_attr_e( 'This will function will break the connection and leave the MainWP Child plugin active and which makes your sites vulnerable. Use only if you attend to reconnect site to the same or a different dashboard right away.', 'mainwp' ); ?>" data-inverted="" data-position="top left"><a href="admin.php?page=MainWPTools&disconnectSites=yes&_wpnonce=<?php echo wp_create_nonce('disconnect_sites'); ?>" onclick="if (!confirm('<?php esc_html_e('Are you sure that you want to disconnect your sites?', 'mainwp');?>')) return false; mainwp_tool_disconnect_sites(); return false;" class="ui button green basic"><?php _e( 'Disconnect Websites', 'mainwp' ); ?></a></div>
					</div>					
                    <?php echo MainWP_UI::render_screen_options(); ?>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php esc_html_e( 'Show favicons', 'mainwp' ); ?></label>
						<div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'If enabled, your MainWP Dashboard will download and show child sites favicons.', 'mainwp' ); ?>" data-inverted="" data-position="bottom left">
							<input type="checkbox" name="mainwp_use_favicon" id="mainwp_use_favicon" <?php echo( ( get_option( 'mainwp_use_favicon', 1 ) == 1 ) ? 'checked="true"' : '' ); ?> />
						</div>
					</div>  
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php esc_html_e( 'Turn off brag button', 'mainwp' ); ?></label>
						<div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'If enabled, Twitter messages will be turn off.', 'mainwp' ); ?>" data-inverted="" data-position="bottom left">
							<input type="checkbox" name="mainwp_hide_twitters_message" id="mainwp_hide_twitters_message" <?php echo( ( get_option( 'mainwp_hide_twitters_message', 0 ) == 1 ) ? 'checked="true"' : '' ); ?> />
						</div>
					</div>
                     <div class="ui grid field">
                        <label class="six wide column middle aligned"><?php esc_html_e( 'Enable Managed Client Reports for WooCommerce', 'mainwp' ); ?></label>
                        <div class="ten wide column ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'Enable Managed Client Reports for WooCommerce', 'mainwp' ); ?>" data-inverted="" data-position="top left">
                            <input type="checkbox" name="enable_managed_cr_for_wc" <?php echo ( ( get_option( 'mainwp_enable_managed_cr_for_wc' ) == 1 ) ? 'checked="true"' : ''); ?> />
                        </div>
                    </div>
					<div class="ui divider"></div>
					<input type="submit" name="submit" id="submit" class="ui green big button right floated" value="<?php esc_attr_e( 'Save Settings', 'mainwp' ); ?>"/>
					<div style="clear:both"></div>
					</form>
				</div>
			</div>
		<?php

		self::renderFooter( 'MainWPTools' );
	}

	public static function exportSites() {
		if ( isset( $_GET['doExportSites'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'export_sites' ) ) {

			$sql 		  = MainWP_DB::Instance()->getSQLWebsitesForCurrentUser( true );
			$websites = MainWP_DB::Instance()->query( $sql );

			if ( ! $websites ) {
				die( "Not found sites" );
			}

			$keys 				  = array( 'name', 'url', 'adminname', 'wpgroups', 'uniqueId', 'http_user', 'http_pass', 'verify_certificate', 'ssl_version' );
			$allowedHeaders = array( 'site name', 'url', 'admin name', 'group', 'security id', 'http username', 'http password', 'verify certificate', 'ssl version' );

			$csv = implode(",", $allowedHeaders) .  "\r\n"; //PHP_EOL;
			@MainWP_DB::data_seek( $websites, 0 );
			while ( $websites && ( $website = @MainWP_DB::fetch_object( $websites ) ) ) {
				if ( empty( $website ) ) {
					continue;
				}
				$row = MainWP_Utility::mapSiteArray( $website, $keys );
				$csv .= '"' . implode('","', $row) . '"' .  "\r\n"; //PHP_EOL;
			}

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=export-sites.csv' );
			echo $csv;
			exit();
		}
	}

	public static function renderReportResponder() {
		if ( !mainwp_current_user_can( 'dashboard', 'manage_dashboard_settings' ) ) {
			mainwp_do_not_have_permissions( __( 'manage dashboard settings', 'mainwp' ) );
			return;
		}

		self::renderHeader( 'SettingsClientReportsResponder' );
		?>
		<div id="mainwp-mcrwc-settings" class="ui segment">
				<?php
				if ( isset( $_POST[ 'save_changes' ] ) || isset( $_POST[ 'reset_connection' ] ) ) {
					$nonce = $_REQUEST[ '_wpnonce' ];
					if ( !wp_verify_nonce( $nonce, 'general_settings' ) ) {
						echo '<div class="ui red message"><i class="close icon"></i>' . __( 'Unable to save settings, please refresh and try again.', 'mainwp' ) . '</div>';
					} else {
						if ( isset( $_POST[ 'reset_connection' ] ) ) {
							MainWP_Utility::update_option( 'live-report-responder-pubkey', '' );
						} else {
							$siteurl = stripslashes( $_POST[ 'live_reponder_site_url' ] );
							if ( !empty($siteurl) && substr( $siteurl, - 1 ) != '/' ) {
								$siteurl = $siteurl . "/";
							}
							update_option( 'live-report-responder-siteurl', $siteurl );
							update_option( 'live-report-responder-provideaccess', ( isset( $_POST[ 'live_reponder_provideaccess' ] ) ) ? $_POST[ 'live_reponder_provideaccess' ] : ''  );
							$security_token = MainWP_Utility::generate_random_string();
							update_option( 'live-reports-responder-security-id', ( isset( $_POST[ 'requireUniqueSecurityId' ] ) ) ? $_POST[ 'requireUniqueSecurityId' ] : ''  );
							update_option( 'live-reports-responder-security-code', stripslashes( $security_token ) );
							echo '<div class="ui green message"><i class="close icon"></i>' . __( 'Settings have been saved successfully!', 'mainwp' ) . '</div>';
						}
					}
				}
			 	?>
				<div class="ui form">
					<form method="POST">
					<?php
					wp_nonce_field( 'general_settings' );
					$pubkey = get_option( 'live-report-responder-pubkey' );
					?>
                    <h3 class="ui dividing header"><?php esc_html_e( 'Managed Client Reports for WooCommerce Settings', 'mainwp' ); ?></h3>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Managed Client Reports site URL', 'mainwp' ); ?></label>
						<div class="ten wide column" data-tooltip="<?php esc_attr_e( 'Enter your WooCommerce reporting site URL here.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
							<input type="text"  name="live_reponder_site_url" placeholder="https://yourwoosite.com/" value="<?php echo esc_attr( get_option( 'live-report-responder-siteurl' ) ); ?>" autocomplete="off" <?php if ( !empty( $pubkey ) ) { echo 'disabled'; } ?> >
						</div>
					</div>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Allow connection', 'mainwp' ); ?></label>
						<div class="ten wide column">
							<div class="ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'Enable to allow connection.', 'mainwp' ); ?>" data-inverted="" data-position="top left">
								<input type="checkbox" name="live_reponder_provideaccess" value="yes" <?php if ( get_option( 'live-report-responder-provideaccess' ) == 'yes' ) echo 'checked'; ?> <?php if ( !empty( $pubkey ) ) { echo 'disabled'; } ?>>
							</div>
						</div>
					</div>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Require unique security ID to secure the connection', 'mainwp' ); ?></label>
						<div class="ten wide column">
							<div class="ui toggle checkbox" data-tooltip="<?php esc_attr_e( 'Enable to generate unique security ID for additional security.', 'mainwp' ); ?>" data-inverted="" data-position="bottom left">
								<input name="requireUniqueSecurityId" type="checkbox" id="requireUniqueSecurityId" <?php if ( get_option( 'live-reports-responder-security-id' ) == 'on' ) echo 'checked'; ?> <?php if ( !empty( $pubkey ) ) { echo 'disabled'; } ?> />
							</div>
						</div>
					</div>
					<?php if ( get_option( 'live-reports-responder-security-id' ) == 'on' ) { ?>
					<div class="ui grid field">
						<label class="six wide column middle aligned"><?php _e( 'Your unique Security ID', 'mainwp' ); ?></label>
						<div class="ten wide column">
							<div class="ui label huge">
							  <i class="key icon"></i>
							  <?php echo get_option( 'live-reports-responder-security-code' ); ?>
							</div>
						</div>
					</div>
					<?php } ?>
					<div class="ui divider"></div>
					<input type="submit" name="save_changes" value="<?php esc_attr_e( 'Save Settings', 'mainwp' ); ?>" class="ui button green big right floated" <?php if ( !empty( $pubkey ) ) { echo 'disabled'; } ?> />
					<?php if ( !empty( $pubkey ) ) { ?>
						<input type="submit" name="reset_connection" value="<?php esc_attr_e( 'Reset Connection', 'mainwp' ); ?>" class="ui button green big basic">
					<?php } ?>
					<div style="clear:both"></div>
					</form>
				</div>
			</div>

		<?php
		self::renderFooter( 'SettingsClientReportsResponder' );
	}

	// Hook the section help content to the Help Sidebar element
	public static function mainwp_help_content() {
		if ( isset( $_GET['page'] ) && ( $_GET['page'] == "Settings" || $_GET['page'] == "SettingsAdvanced" || $_GET['page'] == "MainWPTools" || $_GET['page'] == "SettingsClientReportsResponder" ) ) {
			?>
			<p><?php echo __( 'If you need help with your MainWP Dashboard settings, please review following help documents', 'mainwp' ); ?></p>
			<div class="ui relaxed bulleted list">
				<div class="item"><a href="https://mainwp.com/help/docs/set-up-the-mainwp-plugin/mainwp-dashboard-settings/" target="_blank">MainWP Dashboard Settings</a></div>
			</div>
			<?php
		}
	}

}
