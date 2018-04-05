<?php
require_once("inc/init.inc.php");

if(isset($_GET['action']) && $_GET['action'] == 'deconnexion')
{
    session_destroy();
}

if(internauteEstConnecte()) // si l'internaute n'est pas connecté, il n'a rien à faire sur la page connexion, on le redirige alors vers sa page profil;
{
    header("location:profil.php");
}


// debug($_POST);
if($_POST)
{
    $resultat = $pdo->query("SELECT * FROM membre WHERE pseudo = '$_POST[pseudo]'"); // on selectionne en BDD tous les membres qui possèdent le même pseudo que l'internaute a saisie dans le formulaire;

    if($resultat->rowCount() != 0)
    {
        $membre = $resultat->fetch(PDO::FETCH_ASSOC); // on associe la méthode fetch() pour rendre exploitable le résultat et récupérer les données de l'internaute ayant saisi le bon pseudo;
        // debug($membre);
        // 
        // (password_verify($_POST['mdp'], $membre['mdp']))
        if($membre['mdp'] == $_POST['mdp']) // on contrôle que le mot de passe de la BDD est le même que celui que l'internaute a saisi dans le formulaire;
        {
            foreach($membre as $indice =>$valeur) // on passe ici en revue les informations du membre qui a le bon pseudo et mdp
            {
                if($indice != 'mdp') // on exclu le mdp qui n'est pas conservé dans le fichier session
                {
                    $_SESSION['membre'][$indice] = $valeur; // on créé dans le fichier session un tableau membre et on enregistre sur les données de l'internautre qui pourra dès à présent naviguer sur le site sans être déconnecté;
                }
            }
            // debug($_SESSION);
            header("location:profil.php"); // ayant les bons identifiant, on le redirige vers sa page de profil;
        }
        else // sinon l'internaute a saisi un mauvais mdp
        {
            $content .= '<div class="alert alert-danger col-md-8 col-md-offset-2 text center">Erreur de mot de passe</div>';
        }
    }
    else // sinon l'internaute a saisi un mauvais pseudo
    {
        $content .= '<div class="alert alert-danger col-md-8 col-md-offset-2 text center">Erreur de pseudo</div>';
    }
    
}
require_once("inc/header.inc.php");
echo $content;











?>

<!-- Réaliser un formulaire HTML de connexion (champs pseudo, mot de passe et le bouton submit-->

<form method="post" action="" class="col-md-8 col-md-offset-2">
<h1 class="alert alert-info text-center">Connexion à la boutique</h1>

  <div class="form-group">
    <label for="pseudo">Pseudo </label>
    <input type="text" class="form-control" id="pseudo" name="pseudo" placeholder="Pseudo">
  </div>

  <div class="form-group">
    <label for="mdp">Mot de passe </label>
    <input type="password" class="form-control" id="mdp"  name="mdp" placeholder="Mot de passe">
  </div>

  <button type="submit" class="btn btn-primary col-xs-12">Connexion</button>
</form>


















<?php
require_once("inc/footer.inc.php");
?>