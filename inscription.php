<?php

require_once("inc/init.inc.php");

if(internauteEstConnecte()) // si l'internaute n'est pas connecté, il n'a rien à faire sur la page profil, on le redirige alors vers la page inscription;
{
    header("location:profil.php");
}


// debug($_POST, 1245);

/*
Contrôle des champs suivants :
    - controler la dispo du pseudo,
    - controler la taille des champs,
    - controler le code postal, qu'il soit de type numérique et de 5 ,caractères,
    - contrôler la validité du champs email,

*/

if($_POST)
{
    $erreur = "";
    $verif_pseudo = $pdo->prepare("SELECT * FROM membre WHERE pseudo = :pseudo");
    $verif_pseudo->bindValue(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
    $verif_pseudo->execute();
    if($verif_pseudo->rowCount() > 0)
    {
        $erreur .= '<div class="alert alert-danger col-md-8 col-md-offeset-2 text-center">Pseudo déjà pris !</div>';
    }
    if(strlen($_POST['pseudo']) < 2 || strlen($_POST['pseudo']) > 20)
    {
        $erreur .= '<div class="alert alert-danger col-md-8 col-md-offeset-2 text-center">ATTENTION ! Votre pseudo n\'est pas valide</div>';
    }
    if(strlen($_POST['nom']) < 2 || strlen($_POST['nom']) > 20)
    {
        $erreur .= '<div class="alert alert-danger col-md-8 col-md-offeset-2 text-center">ATTENTION ! Votre nom n\'est pas valide</div>';
    }
    if(strlen($_POST['prenom']) < 2 || strlen($_POST['prenom']) > 20)
    {
        $erreur .= '<div class="alert alert-danger col-md-8 col-md-offeset-2 text-center">ATTENTION ! Votre prénom n\'est pas valide</div>';
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
    {
        $erreur .= '<div class="alert alert-danger col-md-8 col-md-offeset-2 text-center">ATTENTION ! Votre email n\'est pas valide</div><br>';
    }
    if(!is_numeric($_POST['code_postal']) || iconv_strlen($_POST['code_postal']) !== 5)
    {
        $erreur .= '<div class="alert alert-danger col-md-8 col-md-offeset-2 text-center">ATTENTION ! Votre code postal n\'est pas valide</div><br>';
    }
    $content .= $erreur;
    if(empty($erreur))
    {
        $_POST['mdp'] = password_hash($_POST['mdp'], PASSWORD_DEFAULT); // pour ne pas conserver en clar le mdp dans la BDD 
        $nouveau_membre = $pdo->prepare("INSERT INTO membre (pseudo, mdp, nom, prenom, email, civilite, ville, code_postal, adresse) VALUES (:pseudo, :mdp, :nom, :prenom, :email, :civilite, :ville, :code_postal, :adresse)");
        $nouveau_membre->bindValue(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
        $nouveau_membre->bindValue(':mdp', $_POST['mdp'], PDO::PARAM_STR);
        $nouveau_membre->bindValue(':nom', $_POST['nom'], PDO::PARAM_STR);
        $nouveau_membre->bindValue(':prenom', $_POST['prenom'], PDO::PARAM_STR);
        $nouveau_membre->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
        $nouveau_membre->bindValue(':civilite', $_POST['civilite'], PDO::PARAM_STR);
        $nouveau_membre->bindValue(':ville', $_POST['ville'], PDO::PARAM_STR);
        $nouveau_membre->bindValue(':code_postal', $_POST['code_postal'], PDO::PARAM_STR);
        $nouveau_membre->bindValue(':adresse', $_POST['adresse'], PDO::PARAM_STR);

        $nouveau_membre->execute();

        $content .= '<div class="alert alert-success col-md-8 col-md-offeset-2 text-center">Inscription OK ! <a href="connexion.php" class="alert-link">Cliquez ici pour vous connecter</a></div>';
    }
}

require_once("inc/header.inc.php");
echo $content;



/* MON TRAVAIL
if($_POST)
{
    $erreur = "";
    if(!preg_match('#^[a-zA-Z \'\-\.]+$#' , $_POST['prenom']))
    {
        $erreur .= '<div class="error">ATTENTION ! Votre prénom n\'est pas valide</div><br>';
    }
    if(!is_numeric($_POST['code_postal']) || iconv_strlen($_POST['cp']) !== 5)
    {
        $erreur .= '<div class="error">ATTENTION ! Votre code postal n\'est pas valide</div><br>';
    }
    if(iconv_strlen($_POST['pseudo']) <5 || iconv_strlen($_POST['pseudo']) > 20)
    {
        $erreur .= '<div class="error">ATTENTION ! Votre pseudo n\'est pas valide</div><br>';
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) 
    {
        $erreur .= '<div class="error">ATTENTION ! Votre email n\'est pas valide</div><br>';
    }
    if(empty($erreur))
    {
        foreach($_POST as $indice => $valeur)
        {
            echo $indice . ' : ' . $valeur . '<br>';
        }
            echo '<div class="ok">Inscriptions ok !</div>';
    }
    echo $erreur;
}
*/


?>

<!-- Réaliser un formulaire d'inscription correspondant  à la table member de la BDD (sans les champs id_membre, status) -->

<form method="post" action="" class="col-md-8 col-md-offset-2">
<h1 class="alert alert-info text-center">Inscription</h1>

  <div class="form-group">
    <label for="pseudo">Pseudo </label>
    <input type="text" class="form-control" id="pseudo" name="pseudo" placeholder="Pseudo">
  </div>

  <div class="form-group">
    <label for="mdp">Mot de passe </label>
    <input type="password" class="form-control" id="mdp"  name="mdp" placeholder="Mot de passe">
  </div>

  <div class="form-group">
    <label for="nom">Nom </label>
    <input type="text" class="form-control" id="nom"  name="nom" placeholder="Nom">
  </div>

  <div class="form-group">
    <label for="prenom">Prénom </label>
    <input type="text" class="form-control" id="prenom"  name="prenom" placeholder="Prénom">
  </div>

 
  <div class="form-group">
    <label for="email">Email </label>
    <input type="text" class="form-control" id="email" name="email" placeholder="Email">
  </div>
  
  <div class="form-group">
    <label for="exampleInputEmail1">Civilité</label>
    <select class="form-control" name="civilite">
        <option value="m">Homme</option>
        <option value="f">Femme</option>
    </select>
  </div>

  <div class="form-group">
    <label for="ville">Ville </label>
    <input type="text" class="form-control" id="ville" name="ville" placeholder="Ville">
  </div>

  <div class="form-group">
    <label for="code_postal">Code postal </label>
    <input type="text" class="form-control" id="code_postal" name="code_postal" placeholder="Code postal">
  </div>

  <div class="form-group">
    <label for="code_postal">Adresse </label>
    <textarea type="text" class="form-control" rows="3" id="adresse" name="adresse" placeholder="Adresse"></textarea>
  </div>


  <button type="submit" class="btn btn-primary col-xs-12">Inscription</button>
</form>









<?php

require_once("inc/footer.inc.php");

?>