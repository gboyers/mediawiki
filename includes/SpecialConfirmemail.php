<?php

/**
 * Special page allows users to request email confirmation message, and handles
 * processing of the confirmation code when the link in the email is followed
 *
 * @package MediaWiki
 * @subpackage Special pages
 * @author Rob Church <robchur@gmail.com>
 */
 
/**
 * Main execution point
 *
 * @param $par Parameters passed to the page
 */
function wfSpecialConfirmemail( $par ) {
	$form = new EmailConfirmation();
	$form->execute( $par );
}

class EmailConfirmation extends SpecialPage {
	
	function execute( $code ) {
		global $wgUser, $wgOut;
		#$this->setHeaders();
		if( empty( $code ) ) {
			if( $wgUser->isLoggedIn() ) {
				$this->showRequestForm();
			} else {
				$title = Title::makeTitle( NS_SPECIAL, 'Userlogin' );
				$self = Title::makeTitle( NS_SPECIAL, 'Confirmemail' );
				$skin = $wgUser->getSkin();
				$llink = $skin->makeKnownLinkObj( $title, wfMsgHtml( 'loginreqlink' ), 'returnto=' . $self->getPrefixedUrl() );
				$wgOut->addHtml( wfMsgWikiHtml( 'confirmemail_needlogin', $llink ) );
			}
		} else {
			$this->attemptConfirm( $code );
		}
	}
	
	function showRequestForm() {
		global $wgOut, $wgUser, $wgLang, $wgRequest;
		if( $wgRequest->wasPosted() && $wgUser->matchEditToken( $wgRequest->getText( 'token' ) ) ) {
			$message = $wgUser->sendConfirmationMail() ? 'confirmemail_sent' : 'confirmemail_sendfailed';
			$wgOut->addWikiText( wfMsg( $message ) );
		} else {
			if( $wgUser->isEmailConfirmed() ) {
				$time = $wgLang->timeAndDate( $wgUser->mEmailAuthenticated, true );
				$wgOut->addWikiText( wfMsg( 'emailauthenticated', $time ) );
			}
			$wgOut->addWikiText( wfMsg( 'confirmemail_text' ) );
			$self = Title::makeTitle( NS_SPECIAL, 'Confirmemail' );		
			$form  = wfOpenElement( 'form', array( 'method' => 'post', 'action' => $self->getLocalUrl() ) );
			$form .= wfHidden( 'token', $wgUser->editToken() );
			$form .= wfSubmitButton( wfMsgHtml( 'confirmemail_send' ) );
			$form .= wfCloseElement( 'form' );
			$wgOut->addHtml( $form );
		}				
	}
	
	/**
	 * Attempt to confirm the user's email address and show success or failure
	 * as needed; if successful, take the user to log in
	 *
	 * @param $code Confirmation code
	 */
	function attemptConfirm( $code ) {
		global $wgUser, $wgOut;
		$user = User::newFromConfirmationCode( $code );
		if( is_object( $user ) ) {
			if( $user->confirmEmail() ) {
				$message = $wgUser->isLoggedIn() ? 'confirmemail_loggedin' : 'confirmemail_success';
				$wgOut->addWikiText( wfMsg( $message ) );
				if( !$wgUser->isLoggedIn() ) {
					$title = Title::makeTitle( NS_SPECIAL, 'Userlogin' );
					$wgOut->returnToMain( true, $title->getPrefixedText() );
				}
			} else {
				$wgOut->addWikiText( 'confirmemail_error' );
			}
		} else {
			$wgOut->addWikiText( 'confirmemail_invalid' );
		}
	}
	
}

?>
