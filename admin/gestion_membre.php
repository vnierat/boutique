<?php

require_once('../inc/init.inc.php');
if(isset($_GET['action']) && $_GET['action'] == 'modification' )
{
    
    if(!empty($_POST))
    {
    $resultat = $pdo->prepare("UPDATE membre SET pseudo=:pseudo ,mdp=:mdp ,nom=:nom ,prenom=:prenom ,email=:email ,civilite=:civilite ,ville=:ville ,code_postal=:code_postal ,adresse=:adresse WHERE id_membre = '$_GET[id_membre]'");
    
    $erreur = "";
    $verif_pseudo = $pdo->prepare("SELECT * FROM membre WHERE id_membre = :id_membre");
    $verif_pseudo->bindValue(':id_membre', $_POST['id_membre'], PDO::PARAM_STR);
    $verif_pseudo->execute();

    if($verif_pseudo->rowCount() > 0){
        $erreur .= '<div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Pseudo Indisponible !</div>';
    }
    if(iconv_strlen($_POST['pseudo']) < 2 || iconv_strlen($_POST['pseudo']) > 20){
        $erreur .='<div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Taille de pseudo invalide !</div>';
    }
    if(iconv_strlen($_POST['nom']) < 2 || iconv_strlen($_POST['nom']) > 20){
        $erreur .='<div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Taille du nom invalide !</div>';
    }
    if(iconv_strlen($_POST['prenom']) < 2 || iconv_strlen($_POST['prenom']) > 20){
        $erreur .='<div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Taille du prenom invalide !</div>';
    }
    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $erreur .='<div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Email invalide !</div>';
    } 
    if(iconv_strlen($_POST['code_postal']) != 5 || !is_numeric ($_POST['code_postal'])){
        $erreur .='<div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Code postal invalide !</div>';
    }
    $content .= $erreur;
    if(empty($erreur)){
        $resultat->bindValue(':pseudo', $_POST['pseudo'], PDO::PARAM_STR);
        $resultat->bindValue(':mdp', $_POST['mdp'], PDO::PARAM_STR);
        $resultat->bindValue(':nom', $_POST['nom'], PDO::PARAM_STR);
        $resultat->bindValue(':prenom', $_POST['prenom'], PDO::PARAM_STR);
        $resultat->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
        $resultat->bindValue(':civilite', $_POST['civilite'], PDO::PARAM_STR);
        $resultat->bindValue(':ville', $_POST['ville'], PDO::PARAM_STR);
        $resultat->bindValue(':code_postal', $_POST['code_postal'], PDO::PARAM_INT);
        $resultat->bindValue(':adresse', $_POST['adresse'], PDO::PARAM_STR);
        $resultat->execute();
        header('location:gestion_membre.php?action=affichage&modif=effectue');
        }
    }
}
if(isset($_GET['modif']) && $_GET['modif'] == 'effectue'){
    $content .= '<div class="alert alert-success col-md-8 col-md-offset-2 text-center">Le membre a bien été modifié</div>';
}
require_once('../inc/header.inc.php');

?>

<?php

$content .= '<div class="list-group col-md-6 col-md-offset-3">';
$content .= '<h3 class="list-group-item active text-center"> GESTION MEMBRES </h3>';
$content .= '<a href="?action=affichage" class="list-group-item text-center">Affichage des membres</a>';
$content .= '</div>';

echo $content;


 if(isset($_GET['action']) && $_GET['action'] == 'affichage')
 {  
     echo'
    <div class="col-md-8">
    <div class="col-md-12 col-md-offset-2">
    <h2 class="text-center alert alert-info">Liste des membres</h2>
    </div>
        <table class="table">
                <tr><th class="text-center">id membre</th><th class="text-center">Pseudo</th><th class="text-center">Mdp</th>
                <th>Nom</th><th class="text-center">Prenom</th><th class="text-center">Email</th><th class="text-center">Civilité</th><th class="text-center">Ville</th><th class="text-center">Code Postal</th><th class="text-center">adresse</th><th class="text-center">statut</th><th class="text-center">Supprimer</th><th class="text-center">fier</th></tr>
    ';
    $resultat = $pdo->query("SELECT * FROM membre");
    while($membres = $resultat->fetch(PDO::FETCH_ASSOC))
    { 
        echo '<tr>';
        foreach ($membres as $key => $value) {

            echo '<th class="text-center">'.$value.'</th>';

        }
        echo '<td class="text-center"><a href="?action=modification&id_membre='.$membres['id_membre'].'"><span class="glyphicon glyphicon-pencil"></span></a></td>';
        echo '<td class="text-center"><a href="?action=suppression&id_membre='.$membres['id_membre'].'" Onclick="return(confirm(\'En êtes vous certain ?\'));"><span class="glyphicon glyphicon-trash"></span></a></td>';
        echo '</tr>';
    }
 }

if(isset($_GET['action']) && $_GET['action'] == 'modification')
            {
                if(isset($_GET['id_membre'])){
                    $resultat = $pdo->query("SELECT * FROM membre WHERE id_membre = '$_GET[id_membre]'");
                    $membres = $resultat->fetch(PDO::FETCH_ASSOC);
                    // debug($membres);
                echo'
                <form method="post" action="" class="col-md-8 col-md-offset-2">
                    <div class="form-group">
                        <label for="pseudo">Pseudo</label>
                        <input type="text" class="form-control" id="pseudo" name="pseudo" value="'.$membres['pseudo'].'">
                    </div>
                    <div class="form-group">
                        <label for="mdp">Mot De Passe</label>
                        <input type="password" class="form-control" id="mdp" name="mdp" value="'.$membres['mdp'].'">
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="'.$membres['nom'].'">
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prenom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="'.$membres['prenom'].'">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form-control" id="email" name="email" value="'.$membres['email'].'">
                    </div>
                    <div class="form-group">
                    <label for="civilité">Civilité</label>
                        <select class="form-control" id="civilite" name="civilite">
                            <option value="m"';if($membres == 'm'){echo 'selected';}echo '>Homme</option>
                            <option value="f"';if($membres == 'f'){echo 'selected';}echo '>Femme</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" class="form-control" id="ville" name="ville" value="'.$membres['ville'].'">
                    </div>
                    <div class="form-group">
                        <label for="code_postal">Code Postal</label>
                        <input type="text" class="form-control" id="code_postal" name="code_postal" value="'.$membres['code_postal'].'">
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <textarea class="form-control" rows="3" id="adresse" name="adresse">'.$membres['adresse'].'</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary col-xs-12" name="modif">Modifier</button>
                    </form>
                ';
            }
        }



?>
</table>
</div>





<?php

require_once('../inc/footer.inc.php');



?>










