<?php
$errorType = 0 ;
/*
*	1 = Formulaire incohérent
*	2 = Formulaire incomplet
*	3 = Matricule incorrecte
*	4 = Mot de passe incorrecte
*/

$parametresAttendus = array ( 'matricule' , 'motDePasse' ) ;

$em = new elevesManager($bdd) ;

// On fait un strip_tags
clean($_POST) ;
// On vérifie que toutes les clefs sont là
if (	missing_keys( $_POST, $parametresAttendus ) )// Si il manque de des clefs
{
	if (missing_keys( $_POST, $parametresAttendus ) != $parametresAttendus ) // Si il ne manque pas toutes les clefs = la page n'est pas nouvelle
	{
		$errorType = 1 ;
	}
}
else
{
	// On vérifie si toutes les valeurs sont là
	if ( contains_null($_POST) )
	{
		$errorType = 2 ;
	}
	// Le formulaire est bien complet
	else
	{
		// L'étudiant existe-t'il ?
		if ( $em->matriculeExiste($_POST['matricule']) == False )
		{
			$errorType = 3 ;
		}
		else
		{
			// On récupère l'ID de l'étudiant
			$id = $em->getId($_POST['matricule']) ;
			// On charge les données de l'étudiant.
			$profile = $em->get($id) ;
			
			// Le mot de passe est-il bon ?
			if ( my_decrypt($profile->motDePasse(), my_encrypt( $_POST['motDePasse'] ) ) )
			# Non, le mot de passe est faux
			{
				$errorType = 4 ;
				log_add($id, 'Attempt Log-in', 'FAILED') ;
			}
			else
			{
				if ( $profile->statut() == 'nouveau' )
				{
					$errorType = 5 ;
				}
				elseif ( $profile->statut() == 'banni' )
				{
					$errorType = 6 ;
				}
				else
				{
					$_SESSION['profile'] = $em->get($id) ;
                    			log_add($id, 'Attempt Log-in', 'success') ;
                    			
                    			// Set up the automatic redirection to homepage
                    			$page="accueil";
                    			$loggedin = true; # Will enable the loggedin message
                    			header('Location: /?page=accueil');
                    			// Load all variables and objects we will need in the homepage
                    			require( "pages/reflecteurs/m_accueil.php" ) ;
                    			
				}
			}
		}
	}
}

// Determine if the user logged out or not ?
$successDeco = ( ! isset( $_SESSION['profile'] ) AND isset( $_GET['action'] ) AND $_GET['action'] == 'logout' ) ? true : false ;
