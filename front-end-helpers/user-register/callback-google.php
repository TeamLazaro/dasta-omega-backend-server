<?php

$provider = require __DIR__ . '/provider.php';
$userDatabase = __DIR__ . '/../../data/users.json';

$authCode = $_GET[ 'code' ] ?? 0;

if ( $authCode ) {

	$token = $provider->getAccessToken( 'authorization_code', [
		'code' => $authCode
	] );

	try {

		// We got an access token, let's now get the owner details
		$ownerDetails = $provider->getResourceOwner( $token );

		// Use these details to create a new profile
		$userProvider = 'Google';
		$userId = $ownerDetails->getId();
		$userName = $ownerDetails->getName();
		$userEmail = $ownerDetails->getEmail();

		// Add this user to the database
		$users = json_decode( file_get_contents( $userDatabase ), true );
		$userIds = array_column( $users, 'identifier' );
		if ( in_array( $userId, $userIds ) ) {
			header( 'Location: http://dasta.in/' );
			exit;
		}
		$users[ ] = [
			'provider' => 'Google',
			'identifier' => $userId,
			'name' => $userName,
			'email' => $userEmail,
			'role' => 'executive',
			'suspended' => false
		];
		file_put_contents( $userDatabase, json_encode( $users ) );

		header( 'Location: http://dasta.in/' );

	} catch ( Exception $e ) {

		// Failed to get user details
		header( 'Location: http://dasta.in/?r=' . $e->getMessage() );

	}

}
