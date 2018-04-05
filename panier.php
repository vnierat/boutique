<?php
require_once("inc/init.inc.php");


// AJOUT PANIER________________________

if(isset($_POST['ajout_panier']))
{
    // debug($_POST);
    $resultat = $pdo->query("SELECT * FROM produit WHERE id_produit = '$_POST[id_produit]'");
    $produit = $resultat->fetch(PDO::FETCH_ASSOC);
    // debug($produit);

    ajouterProduitPanier($produit['titre'], $_POST['id_produit'], $_POST['quantite'], $produit['prix']);

}

// SUPPRESION PRODUIT________________________
if(isset($_GET['action']) &&  $_GET['action'] == "suppresion") //On ne rentre que seulement dans le cas où l'on clique sur le lien suppression d'un produit;
{
    retirerProduitDuPanier($_GET['id_produit']); // on execute les fonction permettant de l'effacer d'un produit dans le fichier session, on lui envoi en argument l'id

    $resultat = $pdo->prepare("SELECT * FROM produit WHERE id_produit = :id_produit");
    $resultat->bindValue(':id_produit', $_GET['id_produit'], PDO::PARAM_STR);
    $resultat->execute();
    $produit_suppr = $resultat->fetch(PDO::FETCH_ASSOC);

    $content .= '<div class="alert alert-success col-md-8 col-md-offset-2 text-center">Le produit <strong>'.$produit_suppr['titre'].'</strong> a bien été supprimé du panier</div>';
}


// debug($_SESSION);
// VIDER PANIER________________________
if(isset($_GET['action']) && $_GET['action'] == 'vider')
{
    unset($_SESSION['panier']);
}

// PAIEMENT________________________
if(isset($_POST['payer']))
{
    for($i=0; $i < count($_SESSION['panier']['id_produit']); $i++)
    {
        $resultat = $pdo->query("SELECT * FROM produit WHERE id_produit =" . $_SESSION['panier']['id_produit'][$i]);
        $produit = $resultat->fetch(PDO::FETCH_ASSOC);
        $erreur = '';
        
        if($produit['stock'] < $_SESSION['panier']['quantite'][$i])
        {
            $erreur .= '<hr><div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Stock restant du produit <strong>' . $_SESSION['panier']['titre'][$i] . '</strong> : ' . $produit['stock'] . '</div>';
            $erreur .= '<hr><div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Quantité demandée du produit : <strong>' . $_SESSION['panier']['titre'][$i] . '</strong>' . $_SESSION['panier']['quantite'][$i] . '</div>';

            if($produit['stock'] > 0)
            {
                $erreur .= '<hr><div class="alert alert-danger col-md-8 col-md-offset-2 text-center">La quantité du produit <strong>' . $_SESSION['panier']['titre'][$i] . '</strong> a été réduite car notre stock est insuffisant, veuillez vérifier vos achats.</div>';
                $_SESSION['panier']['quantite'][$i] = $produit['stock'];
            }
            else
            {
                $erreur .= '<hr><div class="alert alert-danger col-md-8 col-md-offset-2 text-center">Le produit <strong>' . $_SESSION['panier']['titre'][$i] . '</strong> a été supprimé car nous somme en rupture de stock, veuillez vérifier vos achats.</div>';

                retirerProduitDuPanier($_SESSION['panier']['id_produit'][$i]); $i--; 
            }
            $content .= $erreur;
        }

    } 
    if(empty($erreur))
        {
            $resultat = $pdo->exec("INSERT INTO commande(id_membre, montant, date_enregistrement) VALUES (" . $_SESSION['membre']['id_membre'] . "," . montantTotal() . ", NOW() ".")");
            $id_commande = $pdo->lastinsertId();
            for($i=0; $i< count($_SESSION['panier']['id_produit']); $i++)
            {
                $resultat = $pdo->exec("INSERT INTO details_commande(id_commande, id_produit, quantite, prix) VALUES ($id_commande, " . $_SESSION['panier']['id_produit'][$i] . ", " . $_SESSION['panier']['quantite'][$i]  . "," . $_SESSION['panier']['prix'][$i] . ") ");

                $resultat = $pdo->exec("UPDATE produit SET stock =stock - " . $_SESSION['panier']['quantite'][$i] . " WHERE id_produit = " . $_SESSION['panier']['id_produit'][$i]);
            }
            unset($_SESSION['panier']);
            $content .= '<hr><div class="alert alert-success col-md-8 col-md-offset-2 text-center">Votre commande a bien été validée. Votre numéro de suivi est le <strong>' .  $id_commande . '</strong></div>';
           
        }
}

require_once("inc/header.inc.php");
echo $content;
?>



<div class="col-md-8 col-md-offset-2">
    <table class="table" class="text-center">
        <tr><th colspan="5" class="alert alert-info text-center">PANIER</th></tr>
        <tr><th class="text-center">Titre</th><th class="text-center">Quantité</th><th class="text-center">Prix unitaire</th><th class="text-center">Prix total</th><th class="text-center">Supprimer</th></tr>

        <?php
        if(empty($_SESSION['panier']['id_produit']))
        {
            echo '<tr><td colspan="5"><div class="alert alert-danger text-center">Votre panier est vide</div></td></tr>';
        }
        else
        {
            for($i = 0 ; $i < count($_SESSION['panier']['id_produit']); $i++)
            {
                echo '<tr class="text-center">';
                echo '<td>' . $_SESSION['panier']['titre'][$i] . '</td>';
                echo '<td>' . $_SESSION['panier']['quantite'][$i] . '</td>';
                echo '<td>' . $_SESSION['panier']['prix'][$i] . ' €</td>';
                echo '<td>' . $_SESSION['panier']['prix'][$i]*$_SESSION['panier']['quantite'][$i] . ' €</td>';
                echo '<td><a href="?action=suppression&id_produit=' . $_SESSION['panier']['id_produit'][$i] . '" onClick="return(confirm(\'En êtes vous certain ?\'));"><span class="glyphicon glyphicon-trash"></span></a></td>'; 
                echo '</tr>';
            }
            echo '<tr><th colspan="1" class="text-center">Total</th><th colspan="4" class="text-center">' . montantTotal() . '€</th></tr>';
        

        if(internauteEstConnecte())
        { 
            echo '<form method="post" action="">';
            echo '<tr><td colspan="5"><input type="submit" name ="payer" class="col-xs-12 btn btn-primary" value="Valider le paiement"></td></tr>';
            echo '</form>';
        }
        else
        { 
            echo '<tr><td colspan="5"><div class="alert alert-warning text-center">Veuillez vous <a href="inscription.php" class="alert-link">inscrire</a> ou vous <a href="connexion.php" class="alert-link">connecter</a> pour valider le paiement.</div></td></tr>';
        }
        echo '<tr><td colspan="5"><a href="?action=vider" onClick="return(confirm(\'En êtes vous certain ?\'));"><span class="glyphicon glyphicon-trash"></span> Vider mon panier</a></td></tr>';
    }

        ?>

    </table>


</div>






<?php
require_once("inc/footer.inc.php");
?>