<?php

// Exit If Accessed Directly
if( ! defined( 'ABSPATH' ) ) { exit; }

// ReST API Class
class wpbme_api {

	// Endpoints
	static
		$url_api = 'https://clientapi.benchmarkemail.com/',
		$url_ui = 'https://ui.benchmarkemail.com/',
		$url_apro = 'https://aproapi.benchmarkemail.com/',
		$url_xml = 'https://api.benchmarkemail.com/';

	// Developer Analytics
	static function tracker( $action ) {
		$wpbme_usage_disable = get_option( 'wpbme_usage_disable' );
		if( $wpbme_usage_disable == '1' ) {
			return;
		}
		// Old UA Usage Tracker Was Removed From Here
	}

	// Get All Signup Forms
	static function get_forms() {
		return self::benchmark_query( 'SignupForm/?PageSize=100' );
	}

	// Get JS Link For Signup Form
	static function get_form_data( $id ) {
		return self::benchmark_query( 'SignupForm/' . $id );
	}

	// Vendor Handshake
	static function update_partner() {
		$uri = 'Client/Partner';
		$body = [ 'PartnerLogin' => 'beautomated' ];
		return self::benchmark_query( $uri, 'POST', $body );
	}

	// Get All Contact Lists
	static function get_lists() {
		return self::benchmark_query( 'Contact/' );
	}

	// Creates Signup Form
	static function create_form( $list_id, $list_name, $form_name, $introduction, $button, $fields ) {

		// Make Form
		$body = [
			'Detail' => [
				'Name' => $form_name,
				'Lists' => [ [ 'ID' => $list_id, 'Name' => $list_name ] ],
				'Version' => 4,
			]
		];
		$response = self::benchmark_query( 'SignupForm/', 'POST', $body );
		$form_id = isset( $response->ID ) ? intval( $response->ID ) : '';
		if( ! $form_id ) { return; }

		// Set Form Details
		$set_fields = [];
		$field_translate = [
			'firstname' => 'First Name',
			'middlename' => 'Middle Name',
			'lastname' => 'Last Name',
			'email' => 'Email',
			'field1' => 'Address',
			'field2' => 'City',
			'field3' => 'State',
			'field4' => 'Zip',
			'field5' => 'Country',
			'field6' => 'Phone',
			'field7' => 'Fax',
			'field8' => 'Cell Phone',
			'field9' => 'Company Name',
			'field10' => 'Job Title',
			'field11' => 'Business Phone',
			'field12' => 'Business Fax',
			'field13' => 'Business Address',
			'field14' => 'Business City',
			'field15' => 'Business State',
			'field16' => 'Business Zip',
			'field17' => 'Business Country',
			'field18' => 'Notes',
			'field19' => 'Date 1',
			'field20' => 'Date 2',
			'field21' => 'Extra 3',
			'field22' => 'Extra 4',
			'field23' => 'Extra 5',
			'field24' => 'Extra 6',
		];
		foreach( $fields as $i => $field ) {
			$column = array_search( $field['name'], $field_translate );
			if( $column === false ) { continue; }
			$set_fields[] = [
				'type' => '1',
				'column' => $column,
				'name' => $field['name'],
				'required' => $field['required'] ? 1 : 0,
				'placeholder' => $field['name'],
				'label' => $field['label'],
			];
		}
		$DesignCode = [
			'boxSetting' => [ 'width' => '', 'hasMarketing' => false ],
			'appearance' => [ 'color' => '#ffffff', 'borderColor' => '', 'borderWidth' => '0', 'borderRadius' => '0' ],
			'fields' => [
				0 => [ 'fieldtype' => 'text', 'content' => $introduction ],
				1 => [ 'fieldtype' => 'fields', 'fields' => $set_fields ],
				2 => [
					'fieldtype' => 'button',
					'align' => 'left',
					'width' => 3,
					'height' => 3,
					'bgColor' => 'rgb(33, 41, 45)',
					'borderColor' => 'rgba(0, 0, 0, 0)',
					'borderWidth' => 0,
					'borderRadius' => 2,
					'font' => 'Helvetica, Arial, sans-serif',
					'fontSize' => 14,
					'bold' => 'normal',
					'italic' => 'normal',
					'color' => 'rgb(255, 255, 255)',
					'underline' => '',
					'lineheight' => 1,
					'letterspacing' => '1px',
					'content' => $button,
				],
			],
			'fieldappearance' => [
				'color' => '#ffffff',
				'borderColor' => '#f1f2f2',
				'borderWidth' => '2',
				'labelBold' => 'normal',
				'labelItalic' => 'normal',
				'labelColor' => '#000000',
				'labelAlign' => 'left',
				'fieldBorderRadius' => 0,
				'labelFont' => 'Helvetica, Arial, sans-serif',
				'labelSize' => 14,
				'placeholderFont' => 'Helvetica, Arial, sans-serif',
				'placeholderSize' => 14,
				'placeholderBold' => 'normal',
				'placeholderItalic' => 'normal',
				'placeholderColor' => '#c4c4c4',
				'inputFont' => 'Helvetica, Arial, sans-serif',
				'inputSize' => 14,
				'inputBold' => 'normal',
				'inputItalic' => 'normal',
				'inputColor' => '#000000',
				'answerFont' => 'Helvetica, Arial, sans-serif',
				'answerSize' => 14,
				'answerBold' => 'normal',
				'answerItalic' => 'italic',
				'answerColor' => '#000000',
				'borderRadius' => 0,
				'buttonLightColor' => '#353d41',
				'buttonDarkColor' => '#0d1519',
			],
			'security' => [ 'hasCaptcha' => 0 ],
		];
		$body = [
			'ID' => $form_id,
			'Detail' => [ 'DesignCode' => json_encode( $DesignCode ) ]
		];
		$response = self::benchmark_query( 'SignupForm/' . $form_id, 'PATCH', $body );
		return $form_id;
	}

	// Creates Email Campaign
	static function create_email( $name, $subject, $from_name, $from_email, $email_html, $post ) {

		// Start Setting-up Email Campaign
		$args = [
			'Detail' => [
				'ContactLists' => [],
				'EmailType' => '',
				'FromEmail' => $from_email,
				'FromName' => $from_name,
				'HasWebpageVersion' => 1,
				'LayoutID' => '',
				'Name' => $name,
				'ReplyEmail' => $from_email,
				'Subject' => $subject,
				'TemplateCode' => '',
				'TemplateContent' => '',
				'TemplateID' => '',
				'Version' => 420,
			]
		];

		// Set Contact Lists
		$lists = self::get_lists();
		if( ! is_array( $lists ) ) {
			return 'No Contact Lists';
		}
		$to_lists = [];
		foreach( $lists as $list ) {
			if( empty( $list->ID ) ) {
				continue;
			}
			if( stristr( $list->Name, 'Abandoned' ) ) {
				continue;
			}
			if( stristr( $list->Name, 'Sample' ) ) {
				continue;
			}
			if( stristr( $list->Name, 'Unsubscribe' ) ) {
				continue;
			}
			$to_lists[] = [ 'ID' => $list->ID ];
		}
		$args['Detail']['ContactLists'] = $to_lists;

		// Handle No Lists Found
		if( ! $to_lists ) {
			return 'No Contact Lists';
		}

		// Create Email Campaign
		$response = self::benchmark_query( 'Emails/', 'POST', $args );
		$emailID = empty( $response->ID ) ? '' : intval( $response->ID );
		if( ! $emailID ) { return $response; }

		// Get Format
		$wpbme_email_type = apply_filters( 'wpbme_email_type', 'DD', $post );

		// Email Campaign Setup
		switch( $wpbme_email_type ) {

			// Raw HTML Formatting
			case 'Custom':
				$email_html = sprintf( '<html><body>%s</body></html>', $email_html );
				$email_html = apply_filters( 'wpbme_email_html', $email_html, $post );
				$args['Detail']['EmailType'] = 'Custom';
				$args['Detail']['TemplateCode'] = $email_html;
				$args['Detail']['TemplateContent'] = $email_html; 
				$args['Detail']['TemplateID'] = -1;
				$args['Detail']['Version'] = 402;
				break;

			// Default Drag/Drop Editing
			default:

				// Apply Drag And Drop Template
				$response = self::benchmark_query(
					'Emails/Template/317', 'PATCH', [ 'EmailID' => $emailID ]
				);
				$TemplateContent = empty( $response->TemplateContent ) ? '' : $response->TemplateContent;
				if( ! $TemplateContent ) { return $response; }

				// Set Content To Drag And Drop Template
				$email_html = apply_filters( 'wpbme_email_html', $email_html, $post );
				$TemplateContent = str_replace( '#RSSCONTENT#', $email_html, $TemplateContent );
				$args['Detail']['EmailType'] = 'DD';
				$args['Detail']['LayoutID'] = 10;
				$args['Detail']['TemplateContent'] = $TemplateContent;

				//$args['Detail']['HasPermissionReminderMessage'] = 1;
				//$args['Detail']['IsRSS'] = 1;
				//$args['Detail']['RSSURL'] = strstr( get_permalink( $post->ID ), 'localhost' )
				//		? 'http://woocommerce.com/blog/feed/?withoutcomments=1'
				//		: get_permalink( $post->ID ) . 'feed/?withoutcomments=1';
		}

		// Apply Template To Email Campaign
		$response = self::benchmark_query( 'Emails/' . $emailID, 'PATCH', $args );

		// Return Int
		return $emailID;
	}

	// Talk To Benchmark ReST API
	static function benchmark_query( $uri = '', $method = 'GET', $body = null, $key = null ) {

		// Organize Request
		if( $body ) { $body = json_encode( $body ); }
		$key = $key ? $key : get_option( 'wpbme_key' );
		$headers = [ 'AuthToken' => $key, 'Content-Type' => 'application/json' ];
		$args = [ 'body' => $body, 'headers' => $headers, 'method' => $method ];
		$url = self::$url_api . $uri;

		// Perform And Log Transmission
		$response = wp_remote_request( $url, $args );
		self::logger( $url, $args, $response );

		// Process Response
		if( is_wp_error( $response ) ) { return $response; }
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response );

		// Handle Errors
		if( isset( $response->Response->Error ) && is_array( $response->Response->Error ) ) {
			$errors = '';
			foreach( $response->Response->Error as $error ) {
				$errors .= isset( $error->Message ) ? $error->Message : '';
				$errors .= ' ';
			}
			$errors = trim( $errors );
			if( $errors ) { return $errors; }
		}

		// Return Success
		return isset( $response->Response->Data )
			? $response->Response->Data : $response;
	}

	// Log API Communications
	static function logger( $url, $request, $response ) {
		$wpbme_debug = get_option( 'wpbme_debug' );
		if( ! $wpbme_debug ) { return; }
		if( ! function_exists( 'wc_get_logger' ) ) { return; }
		$logger = wc_get_logger();
		$details = sprintf(
			"==URL==\n%s\n\n==REQUEST==\n%s\n\n==RESPONSE==\n%s",
			$url,
			print_r( $request, true ),
			print_r( $response, true )
		);
		$logger->info( $details, [ 'source' => 'benchmark-email-lite' ] );
	}

	// Gets Temporary Token And API Key From User / Pass
	static function authenticate( $user, $pass ) {

		// Get New Temporary Token From User / Pass
		$response = self::benchmark_query(
			'Client/Authenticate',
			'POST',
			[ 'Username' => $user, 'Password' => $pass ]
		);

		// Handle Auth Error
		if( empty( $response->Response->Token ) ) {
			return [
				'error' => isset( $response->Response->Errors[0]->Message )
					? $response->Response->Errors[0]->Message
					: print_r( $response, true )
			];
		}

		// Begin Output Object
		$output = [];

		// Token Successful
		$output['wpbme_temp_token'] = trim( $response->Response->Token );

		// Use Temporary Token To Get API Key
		$response = self::benchmark_query(
			'Client/Setting', 'GET', null, $output['wpbme_temp_token']
		);
		if( isset( $response->Response->Token ) ) {
			$output['wpbme_key'] = trim( $response->Response->Token );
		}

		// Use Temporary Token To Get AP Token
		$output['wpbme_ap_token'] = self::get_ap_token( $output['wpbme_temp_token'] );

		// Return
		return $output;

	}

	// Authenticate And Redirect Benchmark UI
	static function authenticate_ui_redirect( $destination_uri ) {
		$wpbme_temp_token = get_option( 'wpbme_temp_token' );
		$wpbme_temp_token_ttl = get_option( 'wpbme_temp_token_ttl' );

		// Maybe Refresh Auth Token
		if( $wpbme_temp_token_ttl < current_time( 'timestamp' ) ) {
			$wpbme_temp_token = self::authenticate_ui_renew();
		}

		// Skip Querystring Redirects Due To URL Encoding Bug In The Below API
		if( strchr( $destination_uri, '?' ) ) {
			return untrailingslashit( self::$url_ui ) . $destination_uri;
		}

		// Request UI Auth Redirect
		$url = self::$url_ui . 'xdc/json/login_redirect_using_token';
		$body = sprintf(
			'token=%s&remember-login=1&redir=%s',
			$wpbme_temp_token,
			urlencode( $destination_uri )
		);
		$args = [ 'body' => $body ];
		$response = wp_remote_post( $url, $args );
		self::logger( $url, $args, $response );

		// Process Response
		if( is_wp_error( $response ) ) { return false; }
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response );
		if( empty( $response->redirectURL ) ) { return false; }

		// Output
		return untrailingslashit( self::$url_ui ) . $response->redirectURL;
	}

	// Maybe Renew Temporary Token
	static function authenticate_ui_renew() {
		$wpbme_temp_token = get_option( 'wpbme_temp_token' );
		$response = self::benchmark_query(
			'Client/AuthenticateUseTempToken', 'POST', null, $wpbme_temp_token
		);

		// Handle Error
		if( empty( $response->Response->Token ) ) {
			return $wpbme_temp_token;
		}

		// Success
		$wpbme_temp_token = trim( $response->Response->Token );
		update_option( 'wpbme_temp_token', $wpbme_temp_token );
		update_option( 'wpbme_temp_token_ttl', current_time( 'timestamp' ) + 86400 );
		$wpbme_ap_token = self::get_ap_token( $wpbme_temp_token );
		update_option( 'wpbme_ap_token', $wpbme_ap_token );
		return $wpbme_temp_token;
	}

	// Get New Automation Pro Token
	static function get_ap_token( $wpbme_temp_token ) {
		$url = self::$url_apro . 'api/v1/token/gettoken';
		$body = 'token=' . $wpbme_temp_token;
		$headers = [
			'Authorization: OAuth ' . $wpbme_temp_token,
			'Content-type: application/x-www-form-urlencoded',
			'Content-length: ' . strlen( $body ),
		];
		//$args = [ 'body' => $body, 'headers' => $headers ];
		//$response = wp_remote_post( $url, $args );
		//return print_r( $response, true );
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		$response = curl_exec( $ch );
		self::logger( $url, [ 'headers' => $headers, 'body' => $body ], $response );
		if( ! $response ) { return; }
		$wpbme_ap_token = str_replace( '"', '', trim( $response ) );
		return $wpbme_ap_token;
	}

	// Legacy XML-RPC API
	static function benchmark_query_legacy() {
		require_once( ABSPATH . WPINC . '/class-IXR.php' );
		$url = self::$url_xml . '1.3/';
		$client = new IXR_Client( $url, false, 443, 15 );
		$args = func_get_args();
		call_user_func_array( [ $client, 'query' ], $args );
		$response = $client->getResponse();
		self::logger( $url, $args, $response );
		return $response;
	}
}
