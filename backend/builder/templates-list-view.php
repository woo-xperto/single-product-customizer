<?php
/**
 * Single Product Page Builder - All Templates List View
 *
 * @package Single_Product_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$templates = get_option( 'sppcfw_builder_templates', array() );

// Migrate legacy single template if present
if ( empty( $templates ) ) {
	$legacy = get_option( 'sppcfw_builder_template', array() );
	if ( ! empty( $legacy ) && ! empty( $legacy['layout'] ) ) {
		$default_id               = 'template_default';
		$templates[ $default_id ] = array(
			'id'         => $default_id,
			'title'      => __( 'Default Single Product Template', 'single-product-customizer' ),
			'layout'     => $legacy['layout'],
			'conditions' => isset( $legacy['conditions'] ) ? $legacy['conditions'] : array( 'scope' => 'entire' ),
			'status'     => 'published',
			'updated_at' => isset( $legacy['updated_at'] ) ? $legacy['updated_at'] : current_time( 'mysql' ),
		);
		update_option( 'sppcfw_builder_templates', $templates );
	}
}

// Handle Single Template Move to Trash via GET action
if ( isset( $_GET['action'] ) && ( 'delete' === $_GET['action'] || 'trash' === $_GET['action'] ) && isset( $_GET['template_id'] ) ) {
	$del_id = sanitize_text_field( wp_unslash( $_GET['template_id'] ) );
	$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'sppcfw_delete_template_' . $del_id ) || wp_verify_nonce( $nonce, 'sppcfw_trash_template_' . $del_id ) ) {
		if ( isset( $templates[ $del_id ] ) ) {
			$templates[ $del_id ]['status']     = 'trash';
			$templates[ $del_id ]['updated_at'] = current_time( 'mysql' );
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Template moved to trash successfully.', 'single-product-customizer' ) . '</p></div>';
		}
	}
}

// Handle Single Template Restore via GET action
if ( isset( $_GET['action'] ) && 'restore' === $_GET['action'] && isset( $_GET['template_id'] ) ) {
	$res_id = sanitize_text_field( wp_unslash( $_GET['template_id'] ) );
	$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'sppcfw_restore_template_' . $res_id ) ) {
		if ( isset( $templates[ $res_id ] ) ) {
			$templates[ $res_id ]['status']     = 'draft';
			$templates[ $res_id ]['updated_at'] = current_time( 'mysql' );
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Template restored successfully.', 'single-product-customizer' ) . '</p></div>';
		}
	}
}

// Handle Single Template Delete Permanently via GET action
if ( isset( $_GET['action'] ) && 'delete_permanently' === $_GET['action'] && isset( $_GET['template_id'] ) ) {
	$del_id = sanitize_text_field( wp_unslash( $_GET['template_id'] ) );
	$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'sppcfw_delete_permanently_template_' . $del_id ) ) {
		if ( isset( $templates[ $del_id ] ) ) {
			unset( $templates[ $del_id ] );
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Template deleted permanently.', 'single-product-customizer' ) . '</p></div>';
		}
	}
}

// Handle Single Template Duplication via GET action
if ( isset( $_GET['action'] ) && 'duplicate' === $_GET['action'] && isset( $_GET['template_id'] ) ) {
	$dup_id = sanitize_text_field( wp_unslash( $_GET['template_id'] ) );
	$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'sppcfw_duplicate_template_' . $dup_id ) ) {
		if ( isset( $templates[ $dup_id ] ) ) {
			$new_id               = 'template_' . time();
			$copied               = $templates[ $dup_id ];
			$copied['id']         = $new_id;
			$copied['title']      = $copied['title'] . ' ' . __( '(Copy)', 'single-product-customizer' );
			$copied['updated_at'] = current_time( 'mysql' );
			$templates[ $new_id ] = $copied;
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Template duplicated successfully.', 'single-product-customizer' ) . '</p></div>';
		}
	}
}

// Handle Bulk Actions (Move to Trash, Scope updates)
$bulk_option  = isset( $_REQUEST['sppcfw_bulk_action_option'] ) ? sanitize_text_field( $_REQUEST['sppcfw_bulk_action_option'] ) : '-1';
$template_ids = isset( $_REQUEST['sppcfw_template_ids'] ) ? (array) $_REQUEST['sppcfw_template_ids'] : array();
$bulk_nonce   = isset( $_REQUEST['sppcfw_bulk_delete_nonce'] ) ? sanitize_text_field( $_REQUEST['sppcfw_bulk_delete_nonce'] ) : '';

if ( '-1' !== $bulk_option && ! empty( $template_ids ) ) {
	if ( empty( $bulk_nonce ) || wp_verify_nonce( $bulk_nonce, 'sppcfw_bulk_delete_action' ) ) {
		$ids_to_act = array_map( 'sanitize_text_field', $template_ids );

		if ( 'sppcfw_trash' === $bulk_option || 'sppcfw_delete' === $bulk_option ) {
			foreach ( $ids_to_act as $id ) {
				if ( isset( $templates[ $id ] ) ) {
					$templates[ $id ]['status']     = 'trash';
					$templates[ $id ]['updated_at'] = current_time( 'mysql' );
				}
			}
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Selected templates moved to trash successfully.', 'single-product-customizer' ) . '</p></div>';
		} elseif ( 'sppcfw_restore' === $bulk_option ) {
			foreach ( $ids_to_act as $id ) {
				if ( isset( $templates[ $id ] ) ) {
					$templates[ $id ]['status']     = 'draft';
					$templates[ $id ]['updated_at'] = current_time( 'mysql' );
				}
			}
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Selected templates restored successfully.', 'single-product-customizer' ) . '</p></div>';
		} elseif ( 'sppcfw_delete_permanently' === $bulk_option ) {
			foreach ( $ids_to_act as $id ) {
				unset( $templates[ $id ] );
			}
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Selected templates deleted permanently.', 'single-product-customizer' ) . '</p></div>';
		} elseif ( 'sppcfw_scope_entire' === $bulk_option ) {
			foreach ( $ids_to_act as $id ) {
				if ( isset( $templates[ $id ] ) ) {
					$templates[ $id ]['conditions']['scope'] = 'entire';
					$templates[ $id ]['updated_at']          = current_time( 'mysql' );
				}
			}
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Selected templates scope updated to Entire Website.', 'single-product-customizer' ) . '</p></div>';
		} elseif ( 'sppcfw_scope_category' === $bulk_option ) {
			foreach ( $ids_to_act as $id ) {
				if ( isset( $templates[ $id ] ) ) {
					$templates[ $id ]['conditions']['scope'] = 'category';
					$templates[ $id ]['updated_at']          = current_time( 'mysql' );
				}
			}
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Selected templates scope updated to Specific Category.', 'single-product-customizer' ) . '</p></div>';
		} elseif ( 'sppcfw_scope_product' === $bulk_option ) {
			foreach ( $ids_to_act as $id ) {
				if ( isset( $templates[ $id ] ) ) {
					$templates[ $id ]['conditions']['scope'] = 'product';
					$templates[ $id ]['updated_at']          = current_time( 'mysql' );
				}
			}
			update_option( 'sppcfw_builder_templates', $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Selected templates scope updated to Specific Product.', 'single-product-customizer' ) . '</p></div>';
		}
	}
}

// Calculate Counts for Status Subsubsub Bar & Extract Unique Months
$count_all        = 0;
$count_published  = 0;
$count_draft      = 0;
$count_private    = 0;
$count_pending    = 0;
$count_trash      = 0;
$available_months = array();

foreach ( $templates as $tpl ) {
	$st = ( isset( $tpl['status'] ) && 'trash' === strtolower( $tpl['status'] ) )
		? 'trash'
		: ( ! empty( $tpl['page_settings']['status'] )
			? strtolower( $tpl['page_settings']['status'] )
			: ( ! empty( $tpl['status'] ) ? strtolower( $tpl['status'] ) : 'published' ) );

	if ( 'trash' === $st ) {
		$count_trash++;
	} else {
		$count_all++;
		if ( 'draft' === $st ) {
			$count_draft++;
		} elseif ( 'private' === $st ) {
			$count_private++;
		} elseif ( 'pending' === $st || 'pending review' === $st ) {
			$count_pending++;
		} else {
			$count_published++;
		}
	}

	if ( ! empty( $tpl['updated_at'] ) && 'N/A' !== $tpl['updated_at'] ) {
		$time_stamp = strtotime( $tpl['updated_at'] );
		if ( $time_stamp ) {
			$ym_key                      = date( 'Ym', $time_stamp );
			$ym_label                    = date( 'F Y', $time_stamp );
			$available_months[ $ym_key ] = $ym_label;
		}
	}
}
krsort( $available_months );

// Status, Scope, Date & Search Filter Selections
$current_status      = isset( $_REQUEST['status'] ) ? strtolower( sanitize_text_field( $_REQUEST['status'] ) ) : 'all';
$current_scope_filter = isset( $_REQUEST['sppcfw_filter_scope'] ) ? sanitize_text_field( $_REQUEST['sppcfw_filter_scope'] ) : 'all';
$current_date_filter = isset( $_REQUEST['sppcfw_filter_date'] ) ? sanitize_text_field( $_REQUEST['sppcfw_filter_date'] ) : 'all';
$search_query        = isset( $_REQUEST['search_query'] ) ? trim( sanitize_text_field( $_REQUEST['search_query'] ) ) : '';

// Filter $templates array based on status, scope, date, and search filters
$filtered_templates = array();
foreach ( $templates as $id => $tpl ) {
	$st = ( isset( $tpl['status'] ) && 'trash' === strtolower( $tpl['status'] ) )
		? 'trash'
		: ( ! empty( $tpl['page_settings']['status'] )
			? strtolower( $tpl['page_settings']['status'] )
			: ( ! empty( $tpl['status'] ) ? strtolower( $tpl['status'] ) : 'published' ) );

	if ( 'trash' === $current_status ) {
		if ( 'trash' !== $st ) {
			continue;
		}
	} else {
		if ( 'trash' === $st ) {
			continue;
		}
		if ( 'published' === $current_status && ( 'published' !== $st && 'publish' !== $st ) ) {
			continue;
		}
		if ( 'draft' === $current_status && 'draft' !== $st ) {
			continue;
		}
		if ( 'private' === $current_status && 'private' !== $st ) {
			continue;
		}
		if ( 'pending' === $current_status && ( 'pending' !== $st && 'pending review' !== $st ) ) {
			continue;
		}
	}

	// Scope filter
	$scope = isset( $tpl['conditions']['scope'] ) ? $tpl['conditions']['scope'] : 'entire';
	if ( 'all' !== $current_scope_filter && ! empty( $current_scope_filter ) ) {
		if ( $scope !== $current_scope_filter ) {
			continue;
		}
	}

	// Date filter
	if ( 'all' !== $current_date_filter && ! empty( $current_date_filter ) ) {
		$time_stamp = ! empty( $tpl['updated_at'] ) ? strtotime( $tpl['updated_at'] ) : 0;
		$ym_key     = $time_stamp ? date( 'Ym', $time_stamp ) : '';
		if ( $ym_key !== $current_date_filter ) {
			continue;
		}
	}

	// Search query filter
	if ( ! empty( $search_query ) ) {
		$title = isset( $tpl['title'] ) ? $tpl['title'] : '';
		if ( false === stripos( $title, $search_query ) ) {
			continue;
		}
	}

	$filtered_templates[ $id ] = $tpl;
}

$new_template_url = admin_url( 'admin.php?page=sppcfw-single-page-builder&template_id=new' );
?>

<div class="sppcfw_card sppcfw_overview_card" id="sppcfw_dashboard">
	<div class="sppcfw_card_header">
		<div class="wrap">
			<div class="header_wrapper">
				<span class="sppcfw_page_title"><?php esc_html_e( 'All Single Product Templates', 'single-product-customizer' ); ?></span>
				<div class="button_groups">
					<a href="<?php echo esc_url( $new_template_url ); ?>" class="sppcfw-btn-primary" id="sppcfw_new_template">
						<span class="dashicons dashicons-plus-alt2"></span>
						<?php esc_html_e( 'Add New Template', 'single-product-customizer' ); ?>
					</a>
				</div>
			</div>
			<p><?php esc_html_e( 'View and manage all single product page layout templates and display conditions from your store in one centralized table. Quickly create, edit, duplicate, or organize layout templates with ease.', 'single-product-customizer' ); ?></p>
		</div>
	</div>

	<?php if ( ! empty( $templates ) ) : ?>
		<form action="<?php echo esc_url( admin_url( 'admin.php?page=sppcfw-single-page-builder' ) ); ?>" method="post" id="sppcfw_template_filter_form" class="sppcfw_card_body posts-filter">
			<input type="hidden" name="page" value="sppcfw-single-page-builder" />
			<input type="hidden" name="status" value="<?php echo esc_attr( $current_status ); ?>" />
			<?php wp_nonce_field( 'sppcfw_bulk_delete_action', 'sppcfw_bulk_delete_nonce' ); ?>

			<!-- Status Subsubsub Bar Above TableNav Box -->
			<ul class="subsubsub sppcfw_status_tabs">
				<li class="all">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&status=all' ) ); ?>" class="<?php echo ( 'all' === $current_status || '' === $current_status ) ? 'current' : ''; ?>">
						<?php esc_html_e( 'All', 'single-product-customizer' ); ?> <span class="count">(<?php echo esc_html( $count_all ); ?>)</span>
					</a> |
				</li>
				<li class="published">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&status=published' ) ); ?>" class="<?php echo 'published' === $current_status ? 'current' : ''; ?>">
						<?php esc_html_e( 'Published', 'single-product-customizer' ); ?> <span class="count">(<?php echo esc_html( $count_published ); ?>)</span>
					</a>
				</li>
				<?php if ( $count_draft > 0 ) : ?>
				<li class="draft">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&status=draft' ) ); ?>" class="<?php echo 'draft' === $current_status ? 'current' : ''; ?>">
						<?php esc_html_e( 'Draft', 'single-product-customizer' ); ?> <span class="count">(<?php echo esc_html( $count_draft ); ?>)</span>
					</a>
				</li>
				<?php endif; ?>
				<?php if ( $count_private > 0 ) : ?>
					<li class="private">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&status=private' ) ); ?>" class="<?php echo 'private' === $current_status ? 'current' : ''; ?>">
							<?php esc_html_e( 'Private', 'single-product-customizer' ); ?> <span class="count">(<?php echo esc_html( $count_private ); ?>)</span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $count_pending > 0 ) : ?>
					| <li class="pending">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&status=pending' ) ); ?>" class="<?php echo 'pending' === $current_status ? 'current' : ''; ?>">
							<?php esc_html_e( 'Pending Review', 'single-product-customizer' ); ?> <span class="count">(<?php echo esc_html( $count_pending ); ?>)</span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $count_trash > 0 ) : ?>
					| <li class="trash">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&status=trash' ) ); ?>" class="<?php echo 'trash' === $current_status ? 'current' : ''; ?>">
							<?php esc_html_e( 'Trash', 'single-product-customizer' ); ?> <span class="count">(<?php echo esc_html( $count_trash ); ?>)</span>
						</a>
					</li>
				<?php endif; ?>
			</ul>

			<div class="sppcfw_tablenav_box">
				<div class="sppcfw_tablenav_group">
					<div class="sppcfw_bulk_tablenav">
						<select name="sppcfw_bulk_action_option">
							<option value="-1"><?php esc_html_e( 'Bulk actions', 'single-product-customizer' ); ?></option>
							<?php if ( 'trash' === $current_status ) : ?>
								<option value="sppcfw_restore"><?php esc_html_e( 'Restore', 'single-product-customizer' ); ?></option>
								<option value="sppcfw_delete_permanently"><?php esc_html_e( 'Delete Permanently', 'single-product-customizer' ); ?></option>
							<?php else : ?>
								<option value="sppcfw_trash"><?php esc_html_e( 'Move to Trash', 'single-product-customizer' ); ?></option>
								<option value="sppcfw_scope_entire"><?php esc_html_e( 'Entire Website', 'single-product-customizer' ); ?></option>
								<option value="sppcfw_scope_category"><?php esc_html_e( 'Specific Category', 'single-product-customizer' ); ?></option>
								<option value="sppcfw_scope_product"><?php esc_html_e( 'Specific Product', 'single-product-customizer' ); ?></option>
							<?php endif; ?>
						</select>
						<input type="submit" name="sppcfw_bulk_action" id="sppcfw_doaction" class="button action" value="<?php esc_html_e( 'Apply', 'single-product-customizer' ); ?>">
					</div>
					<div class="sppcfw_status_tablenav">
						<select name="sppcfw_filter_date">
							<option value="all" <?php selected( $current_date_filter, 'all' ); ?>><?php esc_html_e( 'All dates', 'single-product-customizer' ); ?></option>
							<?php foreach ( $available_months as $ym_code => $month_label ) : ?>
								<option value="<?php echo esc_attr( $ym_code ); ?>" <?php selected( $current_date_filter, $ym_code ); ?>><?php echo esc_html( $month_label ); ?></option>
							<?php endforeach; ?>
						</select>
						<input type="submit" name="sppcfw_filter_action" id="sppcfw_post_query_submit" class="button action" value="<?php esc_html_e( 'Filter', 'single-product-customizer' ); ?>">
					</div>
				</div>

				<div class="sppcfw_tablenav_right_side">
					<p class="sppcfw_search_box">
						<input type="search" id="sppcfw_search_query" name="search_query" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_html_e( 'Search Templates...', 'single-product-customizer' ); ?>">
					</p>
					<div class="tablenav-pages one-page">
						<span class="displaying-num"><?php echo esc_html( count( $filtered_templates ) . ' ' . __( 'items', 'single-product-customizer' ) ); ?></span>
					</div>
				</div>
			</div>

			<table class="sppcfw_table wp-list-table widefat fixed striped table-view-list posts" id="sppcfw_templates_table">
				<thead>
					<tr>
						<td id="cb" class="manage-column column-cb check-column">
							<input type="checkbox" id="sppcfw_cb_select_all">
						</td>
						<th scope="col" id="title" class="manage-column column-title column-primary"><?php esc_html_e( 'Template Name', 'single-product-customizer' ); ?></th>
						<th scope="col" id="scope" class="manage-column column-scope"><?php esc_html_e( 'Display Scope', 'single-product-customizer' ); ?></th>
						<th scope="col" id="status" class="manage-column column-status"><?php esc_html_e( 'Status', 'single-product-customizer' ); ?></th>
						<th scope="col" id="date" class="manage-column column-date"><?php esc_html_e( 'Last Updated', 'single-product-customizer' ); ?></th>
						<th scope="col" id="actions" class="manage-column text-right"><?php esc_html_e( 'Actions', 'single-product-customizer' ); ?></th>
					</tr>
				</thead>
				<tbody id="sppcfw_table_list">
					<?php foreach ( $filtered_templates as $t_id => $tpl ) : ?>
						<?php
						$edit_url     = admin_url( 'admin.php?page=sppcfw-single-page-builder&template_id=' . esc_attr( $t_id ) );
						$dup_url      = wp_nonce_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&action=duplicate&template_id=' . esc_attr( $t_id ) ), 'sppcfw_duplicate_template_' . $t_id );
						$del_url      = wp_nonce_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&action=trash&template_id=' . esc_attr( $t_id ) ), 'sppcfw_trash_template_' . $t_id );
						$restore_url  = wp_nonce_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&action=restore&template_id=' . esc_attr( $t_id ) ), 'sppcfw_restore_template_' . $t_id );
						$perm_del_url = wp_nonce_url( admin_url( 'admin.php?page=sppcfw-single-page-builder&action=delete_permanently&template_id=' . esc_attr( $t_id ) ), 'sppcfw_delete_permanently_template_' . $t_id );

						$scope       = isset( $tpl['conditions']['scope'] ) ? $tpl['conditions']['scope'] : 'entire';
						$scope_label = 'entire' === $scope ? __( 'Entire Website', 'single-product-customizer' ) : ( 'category' === $scope ? __( 'Specific Category', 'single-product-customizer' ) : __( 'Specific Product', 'single-product-customizer' ) );
						$scope_class = 'entire' === $scope ? 'sppcfw-scope-entire' : ( 'category' === $scope ? 'sppcfw-scope-category' : 'sppcfw-scope-product' );

						$status = ( isset( $tpl['status'] ) && 'trash' === strtolower( $tpl['status'] ) )
							? 'trash'
							: ( ! empty( $tpl['page_settings']['status'] )
								? strtolower( $tpl['page_settings']['status'] )
								: ( ! empty( $tpl['status'] ) ? strtolower( $tpl['status'] ) : 'published' ) );
						$status_label = ( 'published' === $status || 'publish' === $status ) ? __( 'Published', 'single-product-customizer' ) : ( 'draft' === $status ? __( 'Draft', 'single-product-customizer' ) : ( 'private' === $status ? __( 'Private', 'single-product-customizer' ) : ( 'trash' === $status ? __( 'Trash', 'single-product-customizer' ) : ( ( 'pending' === $status || 'pending review' === $status ) ? __( 'Pending Review', 'single-product-customizer' ) : ucfirst( $status ) ) ) ) );
						$status_class = ( 'published' === $status || 'publish' === $status ) ? 'sppcfw-status-published' : ( 'draft' === $status ? 'sppcfw-status-draft' : ( 'private' === $status ? 'sppcfw-status-private' : ( 'trash' === $status ? 'sppcfw-status-trash' : ( ( 'pending' === $status || 'pending review' === $status ) ? 'sppcfw-status-pending' : 'sppcfw-status-draft' ) ) ) );
						?>
						<tr id="template-<?php echo esc_attr( $t_id ); ?>" class="iedit level-0 post-null status-publish">
							<th scope="row" class="check-column">
								<input type="checkbox" name="sppcfw_template_ids[]" value="<?php echo esc_attr( $t_id ); ?>">
							</th>
							<td class="title column-title column-primary" data-colname="title">
								<a href="<?php echo esc_url( 'trash' === $status ? '#' : $edit_url ); ?>" class="sppcfw-templates-title-link">
									<?php echo esc_html( ! empty( $tpl['title'] ) ? $tpl['title'] : __( 'Untitled Template', 'single-product-customizer' ) ); ?>
								</a>
								<div class="sppcfw-templates-row-actions">
									<?php if ( 'trash' === $status ) : ?>
										<span class="untrash"><a href="<?php echo esc_url( $restore_url ); ?>" class="sppcfw-action-restore"><?php esc_html_e( 'Restore', 'single-product-customizer' ); ?></a> | </span>
										<span class="delete"><a href="<?php echo esc_url( $perm_del_url ); ?>" class="sppcfw-action-delete-permanently" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to permanently delete this template?', 'single-product-customizer' ); ?>')"><?php esc_html_e( 'Delete Permanently', 'single-product-customizer' ); ?></a></span>
									<?php else : ?>
										<span class="edit"><a href="<?php echo esc_url( $edit_url ); ?>" class="sppcfw-action-edit"><?php esc_html_e( 'Edit in Builder', 'single-product-customizer' ); ?></a> | </span>
										<span class="duplicate"><a href="<?php echo esc_url( $dup_url ); ?>" class="sppcfw-action-duplicate"><?php esc_html_e( 'Duplicate', 'single-product-customizer' ); ?></a> | </span>
										<span class="delete"><a href="<?php echo esc_url( $del_url ); ?>" class="sppcfw-action-delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to move this template to trash?', 'single-product-customizer' ); ?>')"><?php esc_html_e( 'Move to Trash', 'single-product-customizer' ); ?></a></span>
									<?php endif; ?>
								</div>
							</td>
							<td class="scope column-scope" data-colname="scope">
								<span class="sppcfw-scope-badge <?php echo esc_attr( $scope_class ); ?>">
									<?php echo esc_html( $scope_label ); ?>
								</span>
							</td>
							<td class="status column-status" data-colname="status">
								<span class="sppcfw-status-badge <?php echo esc_attr( $status_class ); ?>">
									<?php echo esc_html( $status_label ); ?>
								</span>
							</td>
							<td class="date column-date" data-colname="date">
								<span class="sppcfw-updated-date"><?php echo esc_html( isset( $tpl['updated_at'] ) ? $tpl['updated_at'] : 'N/A' ); ?></span>
							</td>
							<td class="actions sppcfw_actions_cell text-right" data-colname="#">
								<?php if ( 'trash' === $status ) : ?>
									<a href="<?php echo esc_url( $restore_url ); ?>" title="<?php esc_attr_e( 'Restore Template', 'single-product-customizer' ); ?>">
										<span class="dashicons dashicons-undo"></span>
									</a>
									<a href="<?php echo esc_url( $perm_del_url ); ?>" title="<?php esc_attr_e( 'Delete Permanently', 'single-product-customizer' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to permanently delete this template?', 'single-product-customizer' ); ?>')">
										<span class="dashicons dashicons-trash"></span>
									</a>
								<?php else : ?>
									<a href="<?php echo esc_url( $edit_url ); ?>" title="<?php esc_attr_e( 'Edit Template', 'single-product-customizer' ); ?>">
										<span class="dashicons dashicons-edit"></span>
									</a>
									<a href="<?php echo esc_url( $del_url ); ?>" title="<?php esc_attr_e( 'Move to Trash', 'single-product-customizer' ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to move this template to trash?', 'single-product-customizer' ); ?>')">
										<span class="dashicons dashicons-trash"></span>
									</a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</form>
	<?php else : ?>
		<div class="sppcfw-templates-empty-box">
			<span class="dashicons dashicons-layout sppcfw-templates-empty-icon"></span>
			<h3 class="sppcfw-templates-empty-title"><?php esc_html_e( 'No Templates Found', 'single-product-customizer' ); ?></h3>
			<p class="sppcfw-templates-empty-desc"><?php esc_html_e( 'You don’t have any single product page templates yet. Click below to create your first template.', 'single-product-customizer' ); ?></p>
			<a href="<?php echo esc_url( $new_template_url ); ?>" class="sppcfw-btn-primary" id="sppcfw_new_template">
				<span class="dashicons dashicons-plus-alt2"></span>
				<?php esc_html_e( 'Add New Template', 'single-product-customizer' ); ?>
			</a>
		</div>
	<?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var selectAllCb = document.getElementById('sppcfw_cb_select_all');
	var itemCbs     = document.querySelectorAll('input[name="sppcfw_template_ids[]"]');

	if (selectAllCb) {
		selectAllCb.addEventListener('change', function() {
			itemCbs.forEach(function(cb) {
				cb.checked = selectAllCb.checked;
			});
		});
	}

	if (itemCbs.length > 0) {
		itemCbs.forEach(function(cb) {
			cb.addEventListener('change', function() {
				var allChecked = Array.from(itemCbs).every(function(c) { return c.checked; });
				if (selectAllCb) {
					selectAllCb.checked = allChecked;
				}
			});
		});
	}
});
</script>
