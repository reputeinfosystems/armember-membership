<?php 

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'ARM_ai_abilities_Lite' ) ) {

	class ARM_ai_abilities_Lite extends ARM_ai_abilities_base_Lite {

		public static function arm_register_abilities() {
			add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'arm_register_ability_categories_callback' ) );
			add_action( 'wp_abilities_api_init', array( __CLASS__, 'arm_register_abilities_callback' ) );

			if ( did_action( 'wp_abilities_api_init' ) && function_exists( 'wp_register_ability' ) ) {
				self::arm_register_ability_categories_callback();
				self::arm_register_abilities_callback();
			}
		}
		
		public static function arm_register_ability_categories_callback() {
			if ( ! function_exists( 'wp_register_ability_category' ) ) {
				return;
			}

			wp_register_ability_category(
				'armember-read',
				array(
					'label'       => __( 'ARMember Read', 'armember-membership' ),
					'description' => __( 'Retrieve ARMember data such as members, subscription plans, transactions, forms, stats, and personal membership profile.', 'armember-membership' ),
				)
			);

			wp_register_ability_category(
				'armember-write',
				array(
					'label'       => __( 'ARMember Write', 'armember-membership' ),
					'description' => __( 'Modify ARMember records such as updating member status and assigning membership plans.', 'armember-membership' ),
				)
			);

			wp_register_ability_category(
				'armember-delete',
				array(
					'label'       => __( 'ARMember Delete', 'armember-membership' ),
					'description' => __( 'Perform cancellation and removal of ARMember subscriptions.', 'armember-membership' ),
				)
			);
		}

		public static function arm_register_abilities_callback() {
			if ( ! function_exists( 'wp_register_ability' ) ) {
				return;
			}
			
			wp_register_ability(
				'armember/list-subscription-plans',
				array(
					'label'               => __( 'List Subscription Plans', 'armember-membership' ),
					'description'         => __( 'List membership subscription plans in ARMember with details including plan type, pricing, and status (requires Manage Plans access).', 'armember-membership' ),
					'category'            => 'armember-read',
					'execute_callback'    => array( __CLASS__, 'arm_ability_list_subscription_plans' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_manage_plans_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'default'              => array(
							'page'     => 1,
							'per_page' => 10,
							'status'   => 'all',
							'search'   => '',
						),
						'properties'           => array(
							'page'     => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'default'     => 1,
								'description' => __( 'Page number for pagination', 'armember-membership' ),
							),
							'per_page' => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'maximum'     => 100,
								'default'     => 10,
								'description' => __( 'Number of plans per page', 'armember-membership' ),
							),
							'status'   => array(
								'type'        => 'string',
								'enum'        => array( 'all', 'active', 'inactive' ),
								'default'     => 'all',
								'description' => __( 'Filter plans by status', 'armember-membership' ),
							),
							'search'   => array(
								'type'        => 'string',
								'default'     => '',
								'maxLength'   => 100,
								'description' => __( 'Search plans by name', 'armember-membership' ),
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array_merge(
							array(
								'items' => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'id'           => array( 'type' => 'integer' ),
											'name'         => array( 'type' => 'string' ),
											'type'         => array( 'type' => 'string' ),
											'amount'       => array( 'type' => 'number' ),
											'status'       => array( 'type' => 'integer' ),
											'status_label' => array( 'type' => 'string' ),
											'payment_type' => array( 'type' => 'string' ),
											'role'         => array( 'type' => 'string' ),
											'description'  => array( 'type' => 'string' ),
											'is_active'    => array( 'type' => 'boolean' ),
										),
									),
								),
							),
							self::arm_pagination_output_schema_properties()
						),
						'required'   => array( 'items', 'total' ),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
					),
				)
			);

			wp_register_ability(
				'armember/get-subscription-plan',
				array(
					'label'               => __( 'Get Subscription Plan Details', 'armember-membership' ),
					'description'         => __( 'Retrieve detailed information of a single ARMember subscription plan by plan ID (requires Manage Plans access).', 'armember-membership' ),
					'category'            => 'armember-read',
					'execute_callback'    => array( __CLASS__, 'arm_ability_get_subscription_plan' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_manage_plans_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'plan_id' => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'description' => __( 'The ID of the subscription plan.', 'armember-membership' ),
							),
						),
						'required'             => array( 'plan_id' ),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'           => array( 'type' => 'integer' ),
							'name'         => array( 'type' => 'string' ),
							'type'         => array( 'type' => 'string' ),
							'amount'       => array( 'type' => 'number' ),
							'status'       => array( 'type' => 'integer' ),
							'status_label' => array( 'type' => 'string' ),
							'payment_type' => array( 'type' => 'string' ),
							'role'         => array( 'type' => 'string' ),
							'description'  => array( 'type' => 'string' ),
							'is_active'    => array( 'type' => 'boolean' ),
							'is_recurring' => array( 'type' => 'boolean' ),
							'options'      => array( 'type' => 'object' ),
						),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
					),
				)
			);

			wp_register_ability(
				'armember/get-my-profile',
				array(
					'label'               => __( 'Get My Profile', 'armember-membership' ),
					'description'         => __( 'Retrieve current logged-in member personal profile details, account status, and registration date.', 'armember-membership' ),
					'category'            => 'armember-read',
					'execute_callback'    => array( __CLASS__, 'arm_ability_get_my_profile' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_logged_in_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'default'              => array(),
						'properties'           => array(),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'user_id'                  => array( 'type' => 'integer' ),
							'username'                 => array( 'type' => 'string' ),
							'email'                    => array( 'type' => 'string' ),
							'display_name'             => array( 'type' => 'string' ),
							'first_name'               => array( 'type' => 'string' ),
							'last_name'                => array( 'type' => 'string' ),
							'user_registered'          => array( 'type' => 'string' ),
							'formatted_registered_date' => array( 'type' => 'string' ),
							'roles'                    => array(
								'type'  => 'array',
								'items' => array( 'type' => 'string' ),
							),
							'status'                   => array( 'type' => 'integer' ),
							'status_label'             => array( 'type' => 'string' ),
						),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
					),
				)
			);
		
			wp_register_ability(
				'armember/get-my-memberships',
				array(
					'label'               => __( 'Get My Memberships', 'armember-membership' ),
					'description'         => __( 'Retrieve active membership plans, start dates, expiration dates, renewal status, and gateway for the logged-in member.', 'armember-membership' ),
					'category'            => 'armember-read',
					'execute_callback'    => array( __CLASS__, 'arm_ability_get_my_memberships' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_logged_in_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'default'              => array(),
						'properties'           => array(),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'total_plans' => array( 'type' => 'integer' ),
							'memberships' => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'properties' => array(
										'plan_id'      => array( 'type' => 'integer' ),
										'plan_name'    => array( 'type' => 'string' ),
										'start_date'   => array( 'type' => 'string' ),
										'expire_date'  => array( 'type' => 'string' ),
										'is_cancelled' => array( 'type' => 'boolean' ),
										'is_recurring' => array( 'type' => 'boolean' ),
										'payment_mode' => array( 'type' => 'string' ),
										'gateway'      => array( 'type' => 'string' ),
										'status'       => array( 'type' => 'string' ),
									),
								),
							),
						),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
					),
				)
			);

			wp_register_ability(
				'armember/get-my-payment-history',
				array(
					'label'               => __( 'Get My Payment History', 'armember-membership' ),
					'description'         => __( 'Retrieve past payment transactions, invoices, amounts paid, and payment receipts for the logged-in member.', 'armember-membership' ),
					'category'            => 'armember-read',
					'execute_callback'    => array( __CLASS__, 'arm_ability_get_my_payment_history' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_logged_in_permission' ),
					'input_schema'        => self::arm_default_pagination_input_schema( 'payments' ),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array_merge(
							array(
								'items' => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'log_id'                 => array( 'type' => 'integer' ),
											'invoice_id'             => array( 'type' => 'string' ),
											'plan_id'                => array( 'type' => 'integer' ),
											'plan_name'              => array( 'type' => 'string' ),
											'gateway'                => array( 'type' => 'string' ),
											'transaction_id'         => array( 'type' => 'string' ),
											'amount'                 => array( 'type' => 'number' ),
											'currency'               => array( 'type' => 'string' ),
											'status'                 => array( 'type' => 'string' ),
											'status_label'           => array( 'type' => 'string' ),
											'payment_date'           => array( 'type' => 'string' ),
											'formatted_payment_date' => array( 'type' => 'string' ),
										),
									),
								),
							),
							self::arm_pagination_output_schema_properties()
						),
						'required'   => array( 'items', 'total' ),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
					),
				)
			);

			wp_register_ability(
				'armember/get-member',
				array(
					'label'               => __( 'Get Member Details', 'armember-membership' ),
					'description'         => __( 'Retrieve full membership profile, active plans, subscription history, and status for any member (Admin only).', 'armember-membership' ),
					'category'            => 'armember-read',
					'execute_callback'    => array( __CLASS__, 'arm_ability_get_member' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_manage_members_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'user_id'  => array(
								'type'        => 'integer',
								'description' => __( 'WordPress User ID', 'armember-membership' ),
							),
							'email'    => array(
								'type'        => 'string',
								'description' => __( 'Member email address (if user_id not provided)', 'armember-membership' ),
							),
							'username' => array(
								'type'        => 'string',
								'description' => __( 'Member username (if user_id not provided)', 'armember-membership' ),
							),
						),
						'default'              => array(),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'user_id'                  => array( 'type' => 'integer' ),
							'username'                 => array( 'type' => 'string' ),
							'email'                    => array( 'type' => 'string' ),
							'display_name'             => array( 'type' => 'string' ),
							'first_name'               => array( 'type' => 'string' ),
							'last_name'                => array( 'type' => 'string' ),
							'user_registered'          => array( 'type' => 'string' ),
							'formatted_registered_date' => array( 'type' => 'string' ),
							'roles'                    => array(
								'type'  => 'array',
								'items' => array( 'type' => 'string' ),
							),
							'primary_status'           => array( 'type' => 'integer' ),
							'primary_status_label'     => array( 'type' => 'string' ),
							'plans'                    => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'properties' => array(
										'plan_id'        => array( 'type' => 'integer' ),
										'plan_name'      => array( 'type' => 'string' ),
										'start_date'     => array( 'type' => 'string' ),
										'expire_date'    => array( 'type' => 'string' ),
										'is_cancelled'   => array( 'type' => 'boolean' ),
										'payment_mode'   => array( 'type' => 'string' ),
										'gateway'        => array( 'type' => 'string' ),
									),
								),
							),
						),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
					),
				)
			);

			wp_register_ability(
				'armember/list-transactions',
				array(
					'label'               => __( 'List Transactions', 'armember-membership' ),
					'description'         => __( 'List ARMember payment transactions and logs across all users (Admin only).', 'armember-membership' ),
					'category'            => 'armember-read',
					'execute_callback'    => array( __CLASS__, 'arm_ability_list_transactions' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_manage_transactions_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'default'              => array(
							'page'     => 1,
							'per_page' => 10,
							'user_id'  => 0,
							'plan_id'  => 0,
							'status'   => 'all',
							'gateway'  => '',
						),
						'properties'           => array(
							'page'     => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'default'     => 1,
								'description' => __( 'Page number for pagination', 'armember-membership' ),
							),
							'per_page' => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'maximum'     => 100,
								'default'     => 10,
								'description' => __( 'Number of transactions per page', 'armember-membership' ),
							),
							'user_id'  => array(
								'type'        => 'integer',
								'minimum'     => 0,
								'default'     => 0,
								'description' => __( 'Filter by member user ID (0 for all)', 'armember-membership' ),
							),
							'plan_id'  => array(
								'type'        => 'integer',
								'minimum'     => 0,
								'default'     => 0,
								'description' => __( 'Filter by plan ID (0 for all)', 'armember-membership' ),
							),
							'status'   => array(
								'type'        => 'string',
								'enum'        => array( 'all', 'success', 'pending', 'canceled', 'failed', 'expired' ),
								'default'     => 'all',
								'description' => __( 'Filter by transaction status', 'armember-membership' ),
							),
							'gateway'  => array(
								'type'        => 'string',
								'default'     => '',
								'maxLength'   => 50,
								'description' => __( 'Filter by payment gateway key, e.g. paypal, stripe, bank_transfer', 'armember-membership' ),
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array_merge(
							array(
								'items' => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'log_id'                  => array( 'type' => 'integer' ),
											'invoice_id'              => array( 'type' => 'string' ),
											'user_id'                 => array( 'type' => 'integer' ),
											'user_email'              => array( 'type' => 'string' ),
											'plan_id'                 => array( 'type' => 'integer' ),
											'plan_name'               => array( 'type' => 'string' ),
											'gateway'                 => array( 'type' => 'string' ),
											'transaction_id'          => array( 'type' => 'string' ),
											'amount'                  => array( 'type' => 'number' ),
											'currency'                => array( 'type' => 'string' ),
											'status'                  => array( 'type' => 'string' ),
											'status_label'            => array( 'type' => 'string' ),
											'payment_type'            => array( 'type' => 'string' ),
											'payment_date'            => array( 'type' => 'string' ),
											'formatted_payment_date'  => array( 'type' => 'string' ),
										),
									),
								),
							),
							self::arm_pagination_output_schema_properties()
						),
						'required'   => array( 'items', 'total' ),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
					),
				)
			);

			wp_register_ability(
				'armember/get-total-members-stats',
				array(
					'label'               => __( 'Get ARMember Dashboard Stats', 'armember-membership' ),
					'description'         => __( 'Retrieve overview statistics for ARMember including total members, active/inactive counts, total plans, and (with transaction management access) revenue. Requires Manage Members or Manage Transactions access.', 'armember-membership' ),
					'category'            => 'armember-read',
					'execute_callback'    => array( __CLASS__, 'arm_ability_get_total_members_stats' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_member_stats_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'default'              => array(),
						'properties'           => array(),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'total_members'      => array( 'type' => 'integer' ),
							'active_members'     => array( 'type' => 'integer' ),
							'inactive_members'   => array( 'type' => 'integer' ),
							'pending_members'    => array( 'type' => 'integer' ),
							'terminated_members' => array( 'type' => 'integer' ),
							'total_plans'        => array( 'type' => 'integer' ),
							'active_plans'       => array( 'type' => 'integer' ),
							'total_transactions' => array( 'type' => 'integer' ),
							'total_revenue'      => array( 'type' => 'number' ),
							'currency'           => array( 'type' => 'string' ),
						),
						'required'   => array( 'total_members', 'total_plans' ),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
					),
				)
			);

			wp_register_ability(
				'armember/update-member-status',
				array(
					'label'               => __( 'Update Member Status', 'armember-membership' ),
					'description'         => __( 'Change a member status in ARMember: 1=Active, 2=Inactive, 3=Pending, 4=Terminated. Terminated cancels all plans of the member and logs them out. Not allowed for administrator accounts or your own account.', 'armember-membership' ),
					'category'            => 'armember-write',
					'execute_callback'    => array( __CLASS__, 'arm_ability_update_member_status' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_manage_members_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'user_id'     => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'description' => __( 'WordPress User ID of the member', 'armember-membership' ),
							),
							'status'      => array(
								'type'        => 'integer',
								'enum'        => array( 1, 2, 3, 4 ),
								'description' => __( 'New status: 1=Active, 2=Inactive, 3=Pending, 4=Terminated', 'armember-membership' ),
							),
							'notify_user' => array(
								'type'        => 'boolean',
								'default'     => false,
								'description' => __( 'Whether to send an activation email to user when activated', 'armember-membership' ),
							),
						),
						'required'             => array( 'user_id', 'status' ),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'success'          => array( 'type' => 'boolean' ),
							'message'          => array( 'type' => 'string' ),
							'user_id'          => array( 'type' => 'integer' ),
							'previous_status'  => array( 'type' => 'integer' ),
							'new_status'       => array( 'type' => 'integer' ),
							'new_status_label' => array( 'type' => 'string' ),
						),
						'required'   => array( 'success', 'message', 'user_id', 'new_status' ),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => false,
							'destructive' => true,
							'idempotent'  => true,
						),
					),
				)
			);

			wp_register_ability(
				'armember/assign-plan-to-member',
				array(
					'label'               => __( 'Assign Plan to Member', 'armember-membership' ),
					'description'         => __( 'Assign an active membership plan to an existing member.', 'armember-membership' ),
					'category'            => 'armember-write',
					'execute_callback'    => array( __CLASS__, 'arm_ability_assign_plan_to_member' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_manage_members_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'user_id' => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'description' => __( 'WordPress User ID of the member', 'armember-membership' ),
							),
							'plan_id' => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'description' => __( 'Subscription Plan ID to assign', 'armember-membership' ),
							),
						),
						'required'             => array( 'user_id', 'plan_id' ),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'success'   => array( 'type' => 'boolean' ),
							'message'   => array( 'type' => 'string' ),
							'user_id'   => array( 'type' => 'integer' ),
							'plan_id'   => array( 'type' => 'integer' ),
							'plan_name' => array( 'type' => 'string' ),
						),
						'required'   => array( 'success', 'message', 'user_id', 'plan_id' ),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => false,
							'destructive' => false,
							'idempotent'  => false,
						),
					),
				)
			);

			wp_register_ability(
				'armember/cancel-member-subscription',
				array(
					'label'               => __( 'Cancel Member Subscription', 'armember-membership' ),
					'description'         => __( 'Cancel an active subscription or membership plan of a member (same access as Manage Members). Not allowed for administrator accounts or your own account.', 'armember-membership' ),
					'category'            => 'armember-delete',
					'execute_callback'    => array( __CLASS__, 'arm_ability_cancel_member_subscription' ),
					'permission_callback' => array( __CLASS__, 'arm_verify_manage_members_permission' ),
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'user_id' => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'description' => __( 'WordPress User ID of the member', 'armember-membership' ),
							),
							'plan_id' => array(
								'type'        => 'integer',
								'minimum'     => 1,
								'description' => __( 'Subscription Plan ID to cancel', 'armember-membership' ),
							),
						),
						'required'             => array( 'user_id', 'plan_id' ),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'success' => array( 'type' => 'boolean' ),
							'message' => array( 'type' => 'string' ),
							'user_id' => array( 'type' => 'integer' ),
							'plan_id' => array( 'type' => 'integer' ),
						),
						'required'   => array( 'success', 'message' ),
					),
					'meta'                => array(
						'public'       => true,
						'show_in_rest' => true,
						'mcp'          => array(
							'public' => true,
							'type'   => 'tool',
						),
						'annotations'  => array(
							'readonly'    => false,
							'destructive' => true,
							'idempotent'  => true,
						),
					),
				)
			);
		}

		public static function arm_ability_list_subscription_plans( $input = array() ) {
			global $wpdb, $ARMemberLite;

			if ( ! self::arm_verify_manage_plans_permission() ) {
				return new WP_Error( 'arm_unauthorized', __( 'Unauthorized to view subscription plans.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$page     = ! empty( $input['page'] ) ? max( 1, absint( $input['page'] ) ) : 1;
			$per_page = ! empty( $input['per_page'] ) ? min( 100, max( 1, absint( $input['per_page'] ) ) ) : 10;
			$status   = isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'all';
			$search   = isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : '';

			$where = 'WHERE arm_subscription_plan_is_delete = 0 AND arm_subscription_plan_post_id = 0 AND arm_subscription_plan_gift_status = 0';

			if ( 'active' === $status ) {
				$where .= ' AND arm_subscription_plan_status = 1';
			} elseif ( 'inactive' === $status ) {
				$where .= ' AND arm_subscription_plan_status = 0';
			}

			if ( ! empty( $search ) ) {
				$where .= $wpdb->prepare( ' AND arm_subscription_plan_name LIKE %s', '%' . $wpdb->esc_like( $search ) . '%' );
			}

			$table_name = $ARMemberLite->tbl_arm_subscription_plans;

			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}` {$where}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$offset = ( $page - 1 ) * $per_page;
			$sql    = $wpdb->prepare( "SELECT * FROM `{$table_name}` {$where} ORDER BY arm_subscription_plan_id DESC LIMIT %d OFFSET %d", $per_page, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows   = $wpdb->get_results( $sql, ARRAY_A );

			$items = array();
			if ( ! empty( $rows ) ) {
				foreach ( $rows as $row ) {
					$plan_id  = (int) $row['arm_subscription_plan_id'];
					if($ARMemberLite->is_arm_pro_active)
					{
						$plan_obj = new ARM_Plan( 0 );
					}
					else{
						$plan_obj = new ARM_Plan_Lite( 0 );
					}
					$plan_obj->init( (object) $row );

					$arm_plan_payment_type = $plan_obj->payment_type;
					$arm_plan_type = __('One Time','armember-membership');
					switch($arm_plan_payment_type){
						case 'free':
							$arm_plan_type = __('Free Membership','armember-membership');
							break;
						case 'one_time':
							$arm_plan_type = __('One Time','armember-membership');
							break;
						case 'recurring':
						case 'subscription':
							$arm_plan_type = __('Recurring','armember-membership');
							break;
						default:
						$arm_plan_type = __('Free Membership','armember-membership');
						break;

					}

					$status_int = (int) $row['arm_subscription_plan_status'];
					$items[]    = array(
						'id'           => $plan_id,
						'name'         => wp_unslash( $row['arm_subscription_plan_name'] ),
						'type'         => $row['arm_subscription_plan_type'] ?? 'free',
						'amount'       => (float) $row['arm_subscription_plan_amount'],
						'status'       => $status_int,
						'status_label' => ( 1 === $status_int ) ? __( 'Active', 'armember-membership' ) : __( 'Inactive', 'armember-membership' ),
						'payment_type' => $arm_plan_type,
						'role'         => $row['arm_subscription_plan_role'] ?? '',
						'description'  => wp_unslash( $row['arm_subscription_plan_description'] ?? '' ),
						'is_active'    => ( 1 === $status_int ),
					);
				}
			}

			$pagination = self::arm_build_pagination_data( $total, $page, $per_page );

			return array_merge( array( 'items' => $items ), $pagination );
		}

		public static function arm_ability_get_subscription_plan( $input = array() ) {
			global $ARMemberLite;
			if ( ! self::arm_verify_manage_plans_permission() ) {
				return new WP_Error( 'arm_unauthorized', __( 'Unauthorized to view subscription plans.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$plan_id = isset( $input['plan_id'] ) ? absint( $input['plan_id'] ) : 0;
			if ( empty( $plan_id ) ) {
				return new WP_Error( 'arm_invalid_plan_id', __( 'A valid plan_id is required.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			
			if($ARMemberLite->is_arm_pro_active)
			{
				$plan = new ARM_Plan( $plan_id );
			}
			else{
				$plan = new ARM_Plan_Lite( $plan_id );
			}
			if ( ! $plan->exists() ) {
				return new WP_Error( 'arm_plan_not_found', sprintf( __( 'Subscription plan with ID %d not found.', 'armember-membership' ), $plan_id ), array( 'status' => 404 ) );
			}

			$arm_plan_payment_type = $plan->payment_type;
			$arm_plan_type = __('One Time','armember-membership');
			switch($arm_plan_payment_type){
				case 'free':
					$arm_plan_type = __('Free Membership','armember-membership');
					break;
				case 'one_time':
					$arm_plan_type = __('One Time','armember-membership');
					break;
				case 'recurring':
				case 'subscription':
					$arm_plan_type = __('Recurring','armember-membership');
					break;
				default:
				$arm_plan_type = __('Free Membership','armember-membership');
				break;

			}

			// $plan->amount is number_format()ed ("1,000.00"), so read the raw DB value.
			$arm_plan_amount = ( isset( $plan->plan_detail->arm_subscription_plan_amount ) ) ? $plan->plan_detail->arm_subscription_plan_amount : str_replace( ',', '', (string) $plan->amount );

			// Plan options are saved from slashed $_POST, so unslash them like the plan name/description.
			$arm_plan_options = is_array( $plan->options ) ? wp_unslash( $plan->options ) : array();

			return array(
				'id'           => (int) $plan->ID,
				'name'         => $plan->name,
				'type'         => $plan->type,
				'amount'       => (float) $arm_plan_amount,
				'status'       => (int) $plan->status,
				'status_label' => ( 1 === (int) $plan->status ) ? __( 'Active', 'armember-membership' ) : __( 'Inactive', 'armember-membership' ),
				'payment_type' => $arm_plan_type,
				'role'         => (string) $plan->plan_role,
				'description'  => (string) $plan->description,
				'is_active'    => $plan->is_active(),
				'is_recurring' => (bool) ( method_exists( $plan, 'is_recurring' ) ? $plan->is_recurring() : false ),
				'options'      => (object) $arm_plan_options,
			);
		}

		public static function arm_ability_get_my_profile( $input = array() ) {
			global $arm_member_forms,$arm_members_class;
			if ( ! is_user_logged_in() ) {
				return new WP_Error( 'arm_unauthorized', __( 'You must be logged in to view your profile.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$uid  = get_current_user_id();
			$user = $arm_members_class->arm_get_member_detail($uid);
			if ( ! $user ) {
				return new WP_Error( 'arm_user_not_found', __( 'User not found.', 'armember-membership' ), array( 'status' => 404 ) );
			}

			$status_val = arm_get_member_status( $uid );
			if ( empty( $status_val ) ) {
				$status_val = 1;
			}

			if($status_val != 1)
			{
				return new WP_Error( 'arm_user_not_active', __( 'Your account is not active. Please contact the administrator.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			//get all fields data
			$dbFormFields = $arm_member_forms->arm_get_db_form_fields( true );
			$arm_user_meta = ( isset( $user->data->user_meta ) && is_array( $user->data->user_meta ) ) ? $user->data->user_meta : array();
			$response = array(
				'user_id'                   => $uid,
				'username'                  => $user->data->user_login,
				'email'                     => $user->data->user_email,
				'display_name'              => $user->data->display_name,
				'first_name'                => (string) get_user_meta( $uid, 'first_name', true ),
				'last_name'                 => (string) get_user_meta( $uid, 'last_name', true ),
				'user_registered'           => (string) $user->data->user_registered,
				'user_url'		    => (string) $user->data->user_url,
				'formatted_registered_date' => self::arm_format_datetime( $user->data->user_registered ),
				'roles'                     => array_values( (array) $user->roles ),
				'status'                    => (int) $status_val,
				'status_label'              => self::arm_get_member_status_label( $status_val ),
			);

			// Core keys already returned above, plus secrets / non-data field types that must never be exposed.
			$arm_skip_meta_keys    = array( 'user_login', 'user_name', 'user_email', 'user_pass', 'repeat_pass', 'user_role', 'display_name', 'first_name', 'last_name', 'avatar', 'profile_cover' ,'user_url');
			$arm_skip_field_types  = array( 'file', 'avatar', 'profile_cover', 'password', 'current_user_pass', 'hidden', 'html', 'section', 'divider', 'roles', 'submit', 'rememberme', 'captcha' );
			$arm_skip_field_types  = apply_filters( 'arm_ai_abilities_profile_skip_field_types', $arm_skip_field_types );
			if ( is_array( $dbFormFields ) ) {
				foreach ( $dbFormFields as $meta => $field_data ) {
					$arm_field_type = isset( $field_data['type'] ) ? $field_data['type'] : '';
					if ( in_array( $meta, $arm_skip_meta_keys, true ) || in_array( $arm_field_type, $arm_skip_field_types, true ) ) {
						continue;
					}
					$response[ $meta ] = ! empty( $arm_user_meta[ $meta ] ) ? $arm_user_meta[ $meta ] : '';
				}
			}

			return $response;
		}

		public static function arm_ability_get_my_memberships( $input = array() ) {
			global $arm_subscription_plans,$arm_payment_gateways,$ARMemberLite;

			if ( ! is_user_logged_in() ) {
				return new WP_Error( 'arm_unauthorized', __( 'You must be logged in to view your memberships.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$uid = get_current_user_id();

			$plan_ids = arm_get_user_meta( $uid, 'arm_user_plan_ids', true );
			$plan_ids = ( ! empty( $plan_ids ) && is_array( $plan_ids ) ) ? array_map( 'intval', $plan_ids ) : array();		
			$all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();		
			$all_plan_ids = array();
			foreach($plan_ids as $arm_user_plan_id)
			{
				if(array_key_exists($arm_user_plan_id, $all_active_plans))
				{
					array_push($all_plan_ids,$arm_user_plan_id);
				}
			}
			$all_plan_names = self::arm_get_plan_names();

			$memberships = array();
			foreach ( $all_plan_ids as $pid ) {
				$meta_key  = 'arm_user_plan_' . $pid;
				$plan_meta = arm_get_user_meta( $uid, $meta_key, true );
				$plan_meta = is_array( $plan_meta ) ? $plan_meta : array();

				$plan_name    = $all_plan_names[ $pid ] ?? ( __( 'Plan #', 'armember-membership' ) . $pid );
				$is_cancelled = ( isset( $plan_meta['arm_cencelled_plan'] ) && 'yes' === $plan_meta['arm_cencelled_plan'] );
				if($ARMemberLite->is_arm_pro_active)
				{					
					$plan_obj     = new ARM_Plan( $pid );
				}
				else{
					$plan_obj     = new ARM_Plan_Lite( $pid );
				}
				$arm_user_payment_gateway = !empty($plan_meta['arm_user_gateway']) ? $plan_meta['arm_user_gateway'] : '';
				$arm_user_payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key($arm_user_payment_gateway);
				$memberships[] = array(
					'plan_id'      => $pid,
					'plan_name'    => $plan_name,
					'start_date'   => self::arm_format_datetime( $plan_meta['arm_start_plan'] ?? '' ),
					'expire_date'  => self::arm_format_datetime( $plan_meta['arm_expire_plan'] ?? '' ),
					'is_cancelled' => $is_cancelled,
					'is_recurring' => (bool) ( method_exists( $plan_obj, 'is_recurring' ) ? $plan_obj->is_recurring() : false ),
					'payment_mode' => (string) ( $plan_meta['arm_payment_mode'] ?? '' ),
					'gateway'      => (string) ( $arm_user_payment_gateway ),
					'status'       => $is_cancelled ? __( 'Cancelled', 'armember-membership' ) : __( 'Active', 'armember-membership' ),
				);
			}

			return array(
				'total_plans' => count( $memberships ),
				'memberships' => $memberships,
			);
		}

		public static function arm_ability_get_my_payment_history( $input = array() ) {
			global $wpdb, $ARMemberLite, $arm_subscription_plans;

			if ( ! is_user_logged_in() ) {
				return new WP_Error( 'arm_unauthorized', __( 'You must be logged in to view your payment history.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$uid      = get_current_user_id();
			$page     = ! empty( $input['page'] ) ? max( 1, absint( $input['page'] ) ) : 1;
			$per_page = ! empty( $input['per_page'] ) ? min( 100, max( 1, absint( $input['per_page'] ) ) ) : 10;

			$table_name = $ARMemberLite->tbl_arm_payment_log;
			$where      = $wpdb->prepare( 'WHERE arm_user_id = %d', $uid );
			$where .= $wpdb->prepare( ' AND arm_is_post_payment = %d AND arm_is_gift_payment = %d', 0, 0 ); //prevent paid post and gift transaction on AI transaction Lists

			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}` {$where}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$offset = ( $page - 1 ) * $per_page;
			$sql    = $wpdb->prepare( "SELECT * FROM `{$table_name}` {$where} ORDER BY arm_log_id DESC LIMIT %d OFFSET %d", $per_page, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows   = $wpdb->get_results( $sql, ARRAY_A );

			$all_plan_names = self::arm_get_plan_names();

			$items = array();
			if ( ! empty( $rows ) ) {
				foreach ( $rows as $row ) {
					$p_id    = (int) $row['arm_plan_id'];
					$items[] = array(
						'log_id'                 => (int) $row['arm_log_id'],
						'invoice_id'             => (string) ( $row['arm_invoice_id'] ?? '' ),
						'plan_id'                => $p_id,
						'plan_name'              => $all_plan_names[ $p_id ] ?? ( __( 'Plan #', 'armember-membership' ) . $p_id ),
						'gateway'                => (string) ( $row['arm_payment_gateway'] ?? '' ),
						'transaction_id'         => (string) ( $row['arm_transaction_id'] ?? '' ),
						'amount'                 => (float) ( $row['arm_amount'] ?? 0 ),
						'currency'               => (string) ( $row['arm_currency'] ?? 'USD' ),
						'status'                 => self::arm_normalize_transaction_status( $row['arm_transaction_status'] ?? '' ),
						'status_label'           => self::arm_get_transaction_status_label( $row['arm_transaction_status'] ?? '' ),
						'payment_date'           => (string) ( $row['arm_payment_date'] ?? '' ),
						'formatted_payment_date' => self::arm_format_datetime( $row['arm_payment_date'] ?? '' ),
					);
				}
			}

			$pagination = self::arm_build_pagination_data( $total, $page, $per_page );
			return array_merge( array( 'items' => $items ), $pagination );
		}

		public static function arm_ability_get_member( $input = array() ) {
			global $arm_subscription_plans,$arm_payment_gateways;

			if ( ! self::arm_verify_manage_members_permission() ) {
				return new WP_Error( 'arm_unauthorized', __( 'Unauthorized to view member details.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$user = null;
			if ( ! empty( $input['user_id'] ) ) {
				$user = get_user_by( 'id', absint( $input['user_id'] ) );
			} elseif ( ! empty( $input['email'] ) ) {
				$user = get_user_by( 'email', sanitize_email( $input['email'] ) );
			} elseif ( ! empty( $input['username'] ) ) {
				$user = get_user_by( 'login', sanitize_user( $input['username'] ) );
			}

			if ( empty( $input['user_id'] ) && empty( $input['email'] ) && empty( $input['username'] ) ) {
				return new WP_Error( 'arm_invalid_params', __( 'Provide user_id, email or username of the member.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			if ( ! $user || ! ( $user instanceof WP_User ) ) {
				return new WP_Error( 'arm_member_not_found', __( 'Member not found with the provided identifier.', 'armember-membership' ), array( 'status' => 404 ) );
			}

			$uid = (int) $user->ID;

			$arm_target_check = self::arm_check_target_member( $uid );
			if ( is_wp_error( $arm_target_check ) ) {
				return $arm_target_check;
			}

			$status_val = arm_get_member_status( $uid );
			if ( empty( $status_val ) ) {
				$status_val = 1;
			}

			$plan_ids = arm_get_user_meta( $uid, 'arm_user_plan_ids', true );
			$plan_ids = ( ! empty( $plan_ids ) && is_array( $plan_ids ) ) ? array_map( 'intval', $plan_ids ) : array();

			$all_plan_names = self::arm_get_plan_names();

			$plans_data = array();
			foreach ( $plan_ids as $pid ) {
				$meta_key  = 'arm_user_plan_' . $pid;
				$plan_meta = arm_get_user_meta( $uid, $meta_key, true );
				$plan_meta = is_array( $plan_meta ) ? $plan_meta : array();

				$plan_name = $all_plan_names[ $pid ] ?? ( __( 'Plan #', 'armember-membership' ) . $pid );
				$arm_user_payment_gateway = !empty($plan_meta['arm_user_gateway']) ? $plan_meta['arm_user_gateway'] : '';
				$arm_user_payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key($arm_user_payment_gateway);
				$plans_data[] = array(
					'plan_id'      => $pid,
					'plan_name'    => $plan_name,
					'start_date'   => self::arm_format_datetime( $plan_meta['arm_start_plan'] ?? '' ),
					'expire_date'  => self::arm_format_datetime( $plan_meta['arm_expire_plan'] ?? '' ),
					'is_cancelled' => ( isset( $plan_meta['arm_cencelled_plan'] ) && 'yes' === $plan_meta['arm_cencelled_plan'] ),
					'payment_mode' => $plan_meta['arm_payment_mode'] ?? '',
					'gateway'      => $arm_user_payment_gateway,
				);
			}

			return array(
				'user_id'                  => $uid,
				'username'                 => $user->user_login,
				'email'                    => $user->user_email,
				'display_name'             => $user->display_name,
				'first_name'               => get_user_meta( $uid, 'first_name', true ),
				'last_name'                => get_user_meta( $uid, 'last_name', true ),
				'user_registered'          => $user->user_registered,
				'formatted_registered_date' => self::arm_format_datetime( $user->user_registered ),
				'roles'                    => array_values( (array) $user->roles ),
				'primary_status'           => (int) $status_val,
				'primary_status_label'     => self::arm_get_member_status_label( $status_val ),
				'plans'                    => $plans_data,
			);
		}

		public static function arm_ability_list_transactions( $input = array() ) {
			global $wpdb, $ARMemberLite, $arm_subscription_plans;

			if ( ! self::arm_verify_manage_transactions_permission() ) {
				return new WP_Error( 'arm_unauthorized', __( 'Unauthorized to view transactions.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$page     = ! empty( $input['page'] ) ? max( 1, absint( $input['page'] ) ) : 1;
			$per_page = ! empty( $input['per_page'] ) ? min( 100, max( 1, absint( $input['per_page'] ) ) ) : 10;
			$user_id  = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : 0;
			$plan_id  = isset( $input['plan_id'] ) ? absint( $input['plan_id'] ) : 0;
			$status   = isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'all';
			$gateway  = isset( $input['gateway'] ) ? sanitize_text_field( $input['gateway'] ) : '';

			$table_name = $ARMemberLite->tbl_arm_payment_log;

			$where = 'WHERE 1=1';

			$where .= $wpdb->prepare( ' AND arm_is_post_payment = %d AND arm_is_gift_payment = %d', 0, 0 ); //prevent paid post and gift transaction on AI transaction Lists

			if ( $user_id > 0 ) {
				$where .= $wpdb->prepare( ' AND arm_user_id = %d', $user_id );
			}

			if ( $plan_id > 0 ) {
				$where .= $wpdb->prepare( ' AND arm_plan_id = %d', $plan_id );
			}

			if ( ! empty( $gateway ) ) {
				$where .= $wpdb->prepare( ' AND arm_payment_gateway = %s', $gateway );
			}

			// Payment log holds both text ('success') and legacy numeric ('1') statuses.
			$where .= self::arm_get_transaction_status_where( $status );

			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}` {$where}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			$offset = ( $page - 1 ) * $per_page;
			$sql    = $wpdb->prepare( "SELECT * FROM `{$table_name}` {$where} ORDER BY arm_log_id DESC LIMIT %d OFFSET %d", $per_page, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows   = $wpdb->get_results( $sql, ARRAY_A );

			$all_plan_names = self::arm_get_plan_names();

			$items = array();
			if ( ! empty( $rows ) ) {
				foreach ( $rows as $row ) {
					$p_id      = (int) $row['arm_plan_id'];
					$plan_name = $all_plan_names[ $p_id ] ?? ( __( 'Plan #', 'armember-membership' ) . $p_id );

					$items[] = array(
						'log_id'                 => (int) $row['arm_log_id'],
						'invoice_id'             => (string) ( $row['arm_invoice_id'] ?? '' ),
						'user_id'                => (int) ( $row['arm_user_id'] ?? 0 ),
						'user_email'             => (string) ( $row['arm_payer_email'] ?? '' ),
						'plan_id'                => $p_id,
						'plan_name'              => $plan_name,
						'gateway'                => (string) ( $row['arm_payment_gateway'] ?? '' ),
						'transaction_id'         => (string) ( $row['arm_transaction_id'] ?? '' ),
						'amount'                 => (float) ( $row['arm_amount'] ?? 0 ),
						'currency'               => (string) ( $row['arm_currency'] ?? 'USD' ),
						'status'                 => self::arm_normalize_transaction_status( $row['arm_transaction_status'] ?? '' ),
						'status_label'           => self::arm_get_transaction_status_label( $row['arm_transaction_status'] ?? '' ),
						'payment_type'           => (string) ( $row['arm_payment_type'] ?? '' ),
						'payment_date'           => (string) ( $row['arm_payment_date'] ?? '' ),
						'formatted_payment_date' => self::arm_format_datetime( $row['arm_payment_date'] ?? '' ),
					);
				}
			}

			$pagination = self::arm_build_pagination_data( $total, $page, $per_page );
			return array_merge( array( 'items' => $items ), $pagination );
		}
	
		public static function arm_ability_get_total_members_stats( $input = array() ) {
			global $wpdb, $ARMemberLite, $arm_members_class, $arm_subscription_plans, $arm_payment_gateways;

			if ( ! self::arm_verify_member_stats_permission() ) {
				return new WP_Error( 'arm_unauthorized', __( 'Unauthorized to view plugin stats.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$total_members    = is_object( $arm_members_class ) ? (int) $arm_members_class->arm_get_all_members_without_administrator( 0, 1 ) : 0;
			$total_active     = is_object( $arm_members_class ) ? (int) $arm_members_class->arm_get_all_members_without_administrator( 1, 1 ) : 0;
			$total_inactive   = is_object( $arm_members_class ) ? (int) $arm_members_class->arm_get_all_members_without_administrator( 2, 1 ) : 0;
			$total_pending    = is_object( $arm_members_class ) ? (int) $arm_members_class->arm_get_all_members_without_administrator( 3, 1 ) : 0;
			$total_terminated = 0;
			if ( is_object( $arm_members_class ) ) {
				// Pro signature is ($type, $only_total, $recent_data, $inactive_type); Lite is ($type, $only_total, $inactive_type).
				$total_terminated = $ARMemberLite->is_arm_pro_active
					? (int) $arm_members_class->arm_get_all_members_without_administrator( 0, 1, 0, array( 4 ) )
					: (int) $arm_members_class->arm_get_all_members_without_administrator( 0, 1, array( 4 ) );
			}

			$all_plans   = is_object( $arm_subscription_plans ) ? $arm_subscription_plans->arm_get_all_subscription_plans() : array();
			$total_plans = is_array( $all_plans ) ? count( $all_plans ) : 0;

			$active_plans_count = 0;
			if ( is_array( $all_plans ) ) {
				foreach ( $all_plans as $p ) {
					if ( isset( $p['arm_subscription_plan_status'] ) && 1 === (int) $p['arm_subscription_plan_status'] ) {
						$active_plans_count++;
					}
				}
			}


			$arm_stats = array(
				'total_members'      => $total_members,
				'active_members'     => $total_active,
				'inactive_members'   => $total_inactive,
				'pending_members'    => $total_pending,
				'terminated_members' => $total_terminated,
				'total_plans'        => $total_plans,
				'active_plans'       => $active_plans_count,
			);

			// Revenue and transaction figures follow the Transactions page access.
			if ( self::arm_verify_manage_transactions_permission() ) {
				$log_tbl            = $ARMemberLite->tbl_arm_payment_log;
				$total_transactions = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$log_tbl}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$arm_success_where  = self::arm_get_transaction_status_where( 'success' );
				$total_revenue      = (float) $wpdb->get_var( "SELECT SUM(arm_amount) FROM `{$log_tbl}` WHERE 1=1 {$arm_success_where}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				$currency = ( is_object( $arm_payment_gateways ) && method_exists( $arm_payment_gateways, 'arm_get_global_currency' ) ) ? $arm_payment_gateways->arm_get_global_currency() : 'USD';

				$arm_stats['total_transactions'] = $total_transactions;
				$arm_stats['total_revenue']      = round( $total_revenue, 2 );
				$arm_stats['currency']           = (string) $currency;
			}

			return $arm_stats;
		}

		public static function arm_ability_update_member_status( $input = array() ) {
			global $arm_global_settings, $arm_email_settings, $arm_subscription_plans, $ARMemberLite;

			if ( ! self::arm_verify_manage_members_permission() ) {
				return new WP_Error( 'arm_unauthorized', __( 'Unauthorized to update member status.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$user_id = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : 0;
			$status  = isset( $input['status'] ) ? absint( $input['status'] ) : 0;

			if ( empty( $user_id ) ) {
				return new WP_Error( 'arm_invalid_user_id', __( 'A valid user_id is required.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			if ( ! in_array( $status, array( 1, 2, 3, 4 ), true ) ) {
				return new WP_Error( 'arm_invalid_status', __( 'Status must be 1 (Active), 2 (Inactive), 3 (Pending), or 4 (Terminated).', 'armember-membership' ), array( 'status' => 400 ) );
			}

			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return new WP_Error( 'arm_member_not_found', __( 'Member does not exist.', 'armember-membership' ), array( 'status' => 404 ) );
			}

			$arm_target_check = self::arm_check_target_member( $user_id, true );
			if ( is_wp_error( $arm_target_check ) ) {
				return $arm_target_check;
			}

			$previous_status = arm_get_member_status( $user_id );

			if ( 1 === $status ) {
				arm_set_member_status( $user_id, 1 );

				if ( ! empty( $input['notify_user'] ) && 1 !== (int) $previous_status && is_object( $arm_global_settings ) && is_object( $arm_email_settings ) ) {
					$arm_global_settings->arm_mailer( $arm_email_settings->templates->on_menual_activation, $user_id );
				}
			} elseif ( 2 === $status ) {
				arm_set_member_status( $user_id, 2, 0 );
			} elseif ( 3 === $status ) {
				arm_set_member_status( $user_id, 3, 0 );
			} elseif ( 4 === $status ) {
				arm_set_member_status( $user_id, 4 );

				if ( is_object( $arm_subscription_plans ) ) {
					$defaultPlanData      = $arm_subscription_plans->arm_default_plan_array();
					$stop_plan_ids        = arm_get_user_meta( $user_id, 'arm_user_plan_ids', true );
					$stop_future_plan_ids = arm_get_user_meta( $user_id, 'arm_user_future_plan_ids', true );

					if ( ! empty( $stop_future_plan_ids ) && is_array( $stop_future_plan_ids ) ) {
						foreach ( $stop_future_plan_ids as $sf_id ) {
							$arm_subscription_plans->arm_add_membership_history( $user_id, $sf_id, 'cancel_subscription', array(), 'terminate' );
							arm_delete_user_meta( $user_id, 'arm_user_plan_' . $sf_id );
						}
						arm_delete_user_meta( $user_id, 'arm_user_future_plan_ids' );
					}

					if ( ! empty( $stop_plan_ids ) && is_array( $stop_plan_ids ) ) {
						foreach ( $stop_plan_ids as $sp_id ) {
							$userPlanDatameta = arm_get_user_meta( $user_id, 'arm_user_plan_' . $sp_id, true );
							$userPlanDatameta = ! empty( $userPlanDatameta ) ? $userPlanDatameta : array();
							$planData         = shortcode_atts( $defaultPlanData, $userPlanDatameta );
							$arm_plan_detail  = $planData['arm_current_plan_detail'];
							$planData['arm_cencelled_plan'] = 'yes';
							arm_update_user_meta( $user_id, 'arm_user_plan_' . $sp_id, $planData );

							// Same flow as ARMember's own terminate in ARM_members_Lite: gateway cancel only for recurring plans, then arm_cancel_subscription for every plan.
							if ( ! empty( $arm_plan_detail ) ) {
								$arm_plan_obj = $ARMemberLite->is_arm_pro_active ? new ARM_Plan( 0 ) : new ARM_Plan_Lite( 0 );
								$arm_plan_obj->init( (object) $arm_plan_detail );
							} else {
								$arm_plan_obj = $ARMemberLite->is_arm_pro_active ? new ARM_Plan( $sp_id ) : new ARM_Plan_Lite( $sp_id );
							}
							if ( $arm_plan_obj->exists() && $arm_plan_obj->is_recurring() ) {
								do_action( 'arm_cancel_subscription_gateway_action', $user_id, $sp_id );
							}
							$arm_subscription_plans->arm_add_membership_history( $user_id, $sp_id, 'cancel_subscription', array(), 'terminate' );
							do_action( 'arm_cancel_subscription', $user_id, $sp_id );
							$arm_subscription_plans->arm_clear_user_plan_detail( $user_id, $sp_id );
						}
					}
				}

				if ( class_exists( 'WP_Session_Tokens' ) ) {
					$sessions = WP_Session_Tokens::get_instance( $user_id );
					$sessions->destroy_all();
				}
			}

			$status_label = self::arm_get_member_status_label( $status );

			return array(
				'success'          => true,
				'message'          => sprintf( __( 'Member status updated to %s.', 'armember-membership' ), $status_label ),
				'user_id'          => $user_id,
				'previous_status'  => (int) $previous_status,
				'new_status'       => $status,
				'new_status_label' => $status_label,
			);
		}

		public static function arm_ability_assign_plan_to_member( $input = array() ) {
			global $wpdb, $arm_subscription_plans, $ARMemberLite;

			if ( ! self::arm_verify_manage_members_permission() ) {
				return new WP_Error( 'arm_unauthorized', __( 'Unauthorized to assign plans.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$user_id = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : 0;
			$plan_id = isset( $input['plan_id'] ) ? absint( $input['plan_id'] ) : 0;

			if ( empty( $user_id ) || empty( $plan_id ) ) {
				return new WP_Error( 'arm_invalid_params', __( 'Both user_id and plan_id are required.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return new WP_Error( 'arm_member_not_found', __( 'Member does not exist.', 'armember-membership' ), array( 'status' => 404 ) );
			}

			if($ARMemberLite->is_arm_pro_active)
			{					
				$plan = new ARM_Plan( $plan_id );
			}
			else{
				$plan = new ARM_Plan_Lite( $plan_id );
			}
			if ( ! $plan->exists() ) {
				return new WP_Error( 'arm_plan_not_found', __( 'Subscription plan does not exist.', 'armember-membership' ), array( 'status' => 404 ) );
			}

			if ( ! $plan->is_active() ) {
				return new WP_Error( 'arm_plan_inactive', __( 'Only an active plan can be assigned to a member.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			$previous_status = arm_get_member_status( $user_id );
			if($previous_status != 1)
			{
				return new WP_Error( 'arm_user_not_active', __( 'The plan can be assigned to active members only.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			// Paid post and gift plans have their own purchase flows.
			$arm_plan_kind = $wpdb->get_row( $wpdb->prepare( "SELECT arm_subscription_plan_post_id, arm_subscription_plan_gift_status FROM `{$ARMemberLite->tbl_arm_subscription_plans}` WHERE arm_subscription_plan_id = %d", $plan_id ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( ! empty( $arm_plan_kind['arm_subscription_plan_post_id'] ) || ! empty( $arm_plan_kind['arm_subscription_plan_gift_status'] ) ) {
				return new WP_Error( 'arm_plan_not_assignable', __( 'Paid post and gift plans cannot be assigned with this action.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			$arm_target_check = self::arm_check_target_member( $user_id );
			if ( is_wp_error( $arm_target_check ) ) {
				return $arm_target_check;
			}

			if ( ! is_object( $arm_subscription_plans ) ) {
				return new WP_Error( 'arm_plan_service_unavailable', __( 'Subscription plan service is not available.', 'armember-membership' ), array( 'status' => 500 ) );
			}

			$result = $arm_subscription_plans->arm_apply_plan_to_member_function( $plan_id, $user_id );
			if ( false === $result ) {
				return new WP_Error( 'arm_assign_failed', __( 'Could not assign plan to member. Administrator accounts cannot be assigned membership plans.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			return array(
				'success'   => true,
				'message'   => sprintf( __( 'Plan "%s" successfully assigned to member.', 'armember-membership' ), $plan->name ),
				'user_id'   => $user_id,
				'plan_id'   => $plan_id,
				'plan_name' => $plan->name,
			);
		}

		public static function arm_ability_cancel_member_subscription( $input = array() ) {
			global $arm_subscription_plans, $ARMemberLite;

			if ( ! self::arm_verify_manage_members_permission() ) {
				return new WP_Error( 'arm_unauthorized', __( 'Unauthorized to cancel subscriptions.', 'armember-membership' ), array( 'status' => 403 ) );
			}

			$user_id = isset( $input['user_id'] ) ? absint( $input['user_id'] ) : 0;
			$plan_id = isset( $input['plan_id'] ) ? absint( $input['plan_id'] ) : 0;

			if ( empty( $user_id ) || empty( $plan_id ) ) {
				return new WP_Error( 'arm_invalid_params', __( 'Both user_id and plan_id are required.', 'armember-membership' ), array( 'status' => 400 ) );
			}

			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return new WP_Error( 'arm_member_not_found', __( 'Member does not exist.', 'armember-membership' ), array( 'status' => 404 ) );
			}

			$arm_target_check = self::arm_check_target_member( $user_id, true );
			if ( is_wp_error( $arm_target_check ) ) {
				return $arm_target_check;
			}

			if ( ! is_object( $arm_subscription_plans ) ) {
				return new WP_Error( 'arm_service_unavailable', __( 'Subscription service unavailable.', 'armember-membership' ), array( 'status' => 500 ) );
			}

			// Without this check the stop routine would run the plan's cancel action (gateway cancel, fallback plan) for a plan the member never had.
			$arm_user_plan_ids = arm_get_user_meta( $user_id, 'arm_user_plan_ids', true );
			$arm_user_plan_ids = ( ! empty( $arm_user_plan_ids ) && is_array( $arm_user_plan_ids ) ) ? array_map( 'intval', $arm_user_plan_ids ) : array();
			if ( ! in_array( $plan_id, $arm_user_plan_ids, true ) ) {
				return new WP_Error( 'arm_plan_not_assigned', __( 'This member does not have the given plan.', 'armember-membership' ), array( 'status' => 404 ) );
			}

			$arm_plan_obj           = $ARMemberLite->is_arm_pro_active ? new ARM_Plan( $plan_id ) : new ARM_Plan_Lite( $plan_id );
			$arm_cancel_plan_action = ( is_array( $arm_plan_obj->options ) && isset( $arm_plan_obj->options['cancel_plan_action'] ) ) ? $arm_plan_obj->options['cancel_plan_action'] : 'immediate';
			$arm_cancel_message     = ( 'on_expire' === $arm_cancel_plan_action )
				? __( 'Subscription plan cancelled. The member keeps access until the current period ends.', 'armember-membership' )
				: __( 'Subscription plan cancelled successfully.', 'armember-membership' );

			$response = $arm_subscription_plans->arm_ajax_stop_user_subscription( $user_id, $plan_id );

			if ( is_array( $response ) && isset( $response['type'] ) && 'success' === $response['type'] ) {
				return array(
					'success' => true,
					'message' => $arm_cancel_message,
					'user_id' => $user_id,
					'plan_id' => $plan_id,
				);
			}

			$error_message = ( is_array( $response ) && ! empty( $response['msg'] ) ) ? $response['msg'] : __( 'Failed to cancel subscription.', 'armember-membership' );
			return new WP_Error( 'arm_cancel_failed', $error_message, array( 'status' => 400 ) );
		}

	}
}
