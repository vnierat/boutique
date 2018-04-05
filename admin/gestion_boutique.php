<?php

require_once('../inc/init.inc.php');

if(!internauteEstConnecteEtEstAdmin())
{// si l'internaute n'est pas admin il n'a rien a faire la et est donc redirigé vers la connexion
    header("location:".URL."connexion.php");
}

// debug($_GET);

//-------- SUPPRESSION PRODUIT
if(isset($_GET['action']) && $_GET['action'] == 'suppression') // on rentre dans la condition seulemenjt dans le cas où l'on clique sur le lien suppression de l'affichage des produits;
{
  $resultat = $pdo->prepare("DELETE FROM produit WHERE id_produit = :id_produit");
  $resultat->bindValue(':id_produit', $_GET['id_produit'], PDO::PARAM_STR);
  $resultat->execute();

  $_GET['action'] = 'affichage'; //sert à rester sur la page quand on supprime un article;

  $content .= '<div class="alert alert-success col-md-8 col-md-offset-2 text-center">Le produit n° <span class="text-success">' . $_GET['id_produit'] . '</span> a bien été supprimé</div>';

}


//-------- ENREGISTREMENT PRODUIT
if(!empty($_POST))
{

      $photo_bdd = '';
      if(isset($_GET['action']) && $_GET['action'] == 'modification' )
      {
        $photo_bdd = $_POST['photo_actuelle']; // si on souhaite conserver la même photo en cas de modification, on affecte la valeur du champs photo 'hidden', CAD l'URL de la photo selectionnée en BDD;
      }
      // debug($_FILES);
      if(!empty($_FILES['photo']['name']))
      {
        $nom_photo = $_POST['reference'] . '-' . $_FILES['photo']['name'];
        // echo $nom_photo
        $photo_bdd = URL . "photo/$nom_photo";
        // echo $photo_bdd;
        $photo_dossier = RACINE_SITE . "/photo/$nom_photo";
        // echo $photo_dossier;
        copy($_FILES['photo']['tmp_name'], $photo_dossier);
      }

      if(isset($_GET['action']) && $_GET['action'] == 'ajout')
      {
        // EXERCICE : réaliser le script permettant de controler la disponibilité de la référence;
        $erreur = '';
        $verif_ref = $pdo->prepare("SELECT * FROM produit WHERE reference = :reference");
        $verif_ref->bindValue(':reference', $_POST['reference'], PDO::PARAM_STR);
        $verif_ref->execute();


        if($verif_ref->rowCount() > 0)
        {
          $erreur .= '<div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Cette référence existe déjà ! Merci de saisir une référence valide.</div>';
        }
        $content .= $erreur;

        if(empty($erreur))
        {
          $resultat = $pdo->prepare("INSERT INTO produit (reference, categorie, titre, description, couleur, taille, public, photo, prix, stock) VALUES (:reference, :categorie, :titre, :description, :couleur, :taille, :public, :photo, :prix, :stock )");

        $content .= '<div class="alert alert-success col-md-8 col-md-offset-2 text-center">Le produit n° <span class="text-success">' . $_POST['reference'] . '</span> a bien été ajouté</div>';
        }
      }
      else
      {
        // EXERCICE : réaliser le script permettant de modifier un produit à l'aide d'une requete préparée;
        $resultat = $pdo->prepare("UPDATE produit SET reference = :reference, categorie = :categorie, titre = :titre, description = :description, couleur = :couleur, taille = :taille, public = :public, photo = :photo, prix = :prix, stock = :stock WHERE id_produit = '$_GET[id_produit]'");

        $content .= '<div class="alert alert-success col-md-8 col-md-offset-2 text-center">Le produit n° <span class="text-success">' . $_GET['id_produit'] . '</span> a bien été modifié</div>';
      }

      if(empty($erreur))
      {
        $resultat->bindValue(':reference', $_POST['reference'], PDO::PARAM_STR);
        $resultat->bindValue(':categorie', $_POST['categorie'], PDO::PARAM_STR);
        $resultat->bindValue(':titre', $_POST['titre'], PDO::PARAM_STR);
        $resultat->bindValue(':description', $_POST['description'], PDO::PARAM_STR);
        $resultat->bindValue(':couleur', $_POST['couleur'], PDO::PARAM_STR);
        $resultat->bindValue(':taille', $_POST['taille'], PDO::PARAM_STR);
        $resultat->bindValue(':public', $_POST['public'], PDO::PARAM_STR);
        $resultat->bindValue(':photo', $photo_bdd, PDO::PARAM_STR);
        $resultat->bindValue(':prix', $_POST['prix'], PDO::PARAM_INT);
        $resultat->bindValue(':stock', $_POST['stock'], PDO::PARAM_INT);

        $resultat->execute();
      }  
}


// -- LIENS PRODUITS

$content .= '<div class="list-group col-md-6 col-md-offset-3">';
$content .= '<h3 class="list-group-item active text-center">BACK OFFICE</h3>';
$content .= '<a href="?action=affichage" class="list-group-item text-center">Affichage produit</a>';
$content .= '<a href="?action=ajout" class="list-group-item text-center">Ajout produit</a>';
$content .= '<hr></div>';

// -- AFFICHAGE PRODUITS

if(isset($_GET['action']) && $_GET['action'] == 'affichage')
{
  // EXO : afficher l'ensemble de la table produit sous forme d'un tableau HTML, prévoir un lien modification et suppression pour chaque produit.

  $resultat = $pdo->query("SELECT * FROM produit");

  $content .= '<div class="col-md-10 col-md-offset-1"><h3 class="alert alert-success">Affichage produits</h3>';

  $content .= 'Nombre de produits(s) dans la boutique : <span class="badge text-danger">' . $resultat->rowCount() . '</span></div>';

  $content .= '<table class="col-md-10 table" style=: margin-top: 10px ><tr>';
  for($i = 0 ; $i < $resultat->columnCount(); $i++)
  {
    $colonne = $resultat->getColumnMeta($i);
    $content .= '<th>' . $colonne['name'] . '</th>';
  }

  $content .= '<th>Modification</th>';
  $content .= '<th>Suppression</th>';
  $content .= '</tr>';

  while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC))
  {  
    $content .= '<tr>';
    foreach($ligne as $indice=>$valeur) 
    {
      if($indice == 'photo')
        {
          $content .= '<td><img src="' . $valeur . '" alt="" width= "70" height="70"></td>';
        } 
        else 
        {
          $content .= '<td>' . $valeur . '</td>';
        }
      }
      $content .= '<td class="text-center"><a href="?action=modification&id_produit= ' . $ligne['id_produit'] . ' "><span class="glyphicon glyphicon-pencil"></span></a></td>';
      $content .= '<td class="text-center"><a href="?action=suppression&id_produit= ' . $ligne['id_produit'] . ' "Onclick="return(confirm(\'En êtes vous certain ?\'));"><span class="glyphicon glyphicon-trash"></span></a></td>';
      $content .= '</tr>';
  }
$content .= '</table>';
}

  
  





require_once('../inc/header.inc.php');
echo $content;

if(isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'modification'))
{
  if(isset($_GET['id_produit']))
  {
    $resultat = $pdo->prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
    $resultat->bindValue(':id_produit', $_GET['id_produit'], PDO::PARAM_INT);
    $resultat->execute();

    $produit_actuel = $resultat->fetch(PDO::FETCH_ASSOC);
    // debug($produit_actuel);
  }


  // si l'id_produit est défini dans la BDD, on l'affiche sinon on affiche une chaine de caractère vide;
  $id_produit = (isset($produit_actuel['id_produit'])) ? $produit_actuel['id_produit'] : '';
  $reference = (isset($produit_actuel['reference'])) ? $produit_actuel['reference'] : '';
  $categorie = (isset($produit_actuel['categorie'])) ? $produit_actuel['categorie'] : '';
  $titre = (isset($produit_actuel['titre'])) ? $produit_actuel['titre'] : '';
  $description = (isset($produit_actuel['description'])) ? $produit_actuel['description'] : '';
  $couleur = (isset($produit_actuel['couleur'])) ? $produit_actuel['couleur'] : '';
  $taille = (isset($produit_actuel['taille'])) ? $produit_actuel['taille'] : '';
  $public = (isset($produit_actuel['public'])) ? $produit_actuel['public'] : '';
  $photo = (isset($produit_actuel['photo'])) ? $produit_actuel['photo'] : '';
  $prix = (isset($produit_actuel['prix'])) ? $produit_actuel['prix'] : '';
  $stock = (isset($produit_actuel['stock'])) ? $produit_actuel['stock'] : '';

echo 
  '<form method="post" action="" enctype="multipart/form-data" class="col-md-8 col-md-offset-2">
  <h1 class="alert alert-info text-center">' . ucfirst($_GET['action']) . ' produit</h1>

  <input type="hidden" id="id_produit" name="id_produit" value="' . $id_produit . '">

    <div class="form-group">
      <label for="reference">Reference</label>
      <input type="text" class="form-control" id="reference" name="reference"placeholder="Reference" value="' . $reference . '">
    </div>
    <div class="form-group">
      <label for="categorie">Categorie</label>
      <input type="text" class="form-control" id="categorie" name="categorie" placeholder="Categorie" value="' . $categorie . '">
    </div>
    <div class="form-group">
      <label for="titre">Titre</label>
      <input type="text" class="form-control" id="titre" name="titre" placeholder="Titre" value="' . $titre . '">
    </div>
    <div class="form-group">
      <label for="description">Description</label>
      <textarea class="form-control" rows="3" id="description" name="description" > "' . $description .'" </textarea>
    </div>
    <div class="form-group">
      <label for="couleur">Couleur</label>
      <input type="text" class="form-control" id="couleur" name="couleur" placeholder="Couleur" value="' . $couleur . '">
    </div>
    <div class="form-group">
    <label for="taille">Taille</label>
      <select class="form-control" id="taille" name="taille">
          <option value="s"';if($public == 's') echo 'selected'; echo '>S</option>
          <option value="m"';if($public == 'm') echo 'selected'; echo '>M</option>
          <option value="l"';if($public == 'l') echo 'selected'; echo '>L</option>
          <option value="xl"';if($public == 'xl') echo 'selected'; echo '>XL</option>
      </select>
    </div>
    <div class="form-group">
    <label for="public">Public</label>
      <select class="form-control" id="public" name="public">
          <option value="m"';if($public == 'm') echo 'selected'; echo '>Homme</option>
          <option value="f"';if($public == 'f') echo 'selected'; echo '>Femme</option>
          <option value="mixte"';if($public == 'mixte') echo 'selected'; echo '>Mixte</option>
      </select>
    </div>
    <div class="form-group">
      <label for="photo">Photo</label>
      <input type="file" class="form-control" id="photo" name="photo" value="' . $photo. '"><br>';
      if(!empty($photo))
      {
        echo '<em>Vous pouver uploader une nouvelle photo si vous souhaitez la changer</em><br>';
        echo '<img src="' . $photo . '" width="90" height="90" value="' . $photo .'"><br>';
      }
      echo '<input type="hidden" id="photo_actuelle" name="photo_actuelle" value="' . $photo . '">';
      echo '
    </div>
    <div class="form-group">
      <label for="prix">Prix</label>
      <input type="text" class="form-control" id="prix" name="prix" placeholder="Prix" value="' . $prix . '">
    </div>
    <div class="form-group">
      <label for="stock">Stock</label>
      <input type="text" class="form-control" id="stock" name="stock" placeholder="Stock" value="' . $stock . '">
    </div>
    <button type="submit" class="btn btn-primary col-xs-12">' . ucfirst($_GET['action']) . '</button>
  </form>';
}


require_once('../inc/footer.inc.php');
?>