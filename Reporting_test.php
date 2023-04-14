


<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
   ####tableau de bord de demande de stage paeej
   ####Auteur: La Neuve Kaburungu
   ### laneuve@mediabox.bi
   ### le 18/11/2022
   ###Le tableau de bord comporte 2 rapports:
   ###Le premier montre les demandeurs par domaine
   ###Le deuxième montre les demandeurs par sexe

   ###NB:Le controller doit avoir le meme nom avec la vue 
            ##Controller:Addition.php
            ####View:Addition_View.php 
class Controller_Model_Reporting extends CI_Controller {

  #### fonction pour les couleurs des rapports(Après chaque reflesh les rapports changent les couleurs)
    public function getcolor() 
           {
        $chars = 'ABCDEF0123456789';
        $color = '#';
        for ( $i= 0; $i < 6; $i++ ) {
            $color.= $chars[rand(0, strlen($chars) -1)];
           }
        return $color;
        }
      #### appel des filtres dans la fonction index qui ne dependent pas des autres
    public function index(){

      $provinces=$this->Model->getRequete('SELECT provinces.PROVINCE_ID,provinces.PROVINCE_NAME FROM provinces ORDER BY PROVINCE_NAME');

      $PROVINCE_ID=$this->input->post('PROVINCE_ID');
       

  ####Envoie des donnees dans la vue
   $data['provinces']=$provinces;   
    #### appel de la vue
    $this->load->view('View_Model_Reporting',$data);
        }

  ####Fonction pour les rapports(ca comporte les requêtes pour les rapports,les series pour les rapports et les filtres qui dependent des autres)
public function get_rapport(){
   ####declaration des differentes variables

         $SELECTION=$this->input->post('SELECTION');
          $titre="Demandeurs";
###declaration des differentes variables(toutes les variables doivent avoir un sens suivant ce qu'elles vont stocker)
          $PROVINCE_ID=$this->input->post('PROVINCE_ID');
             $criteres="";
             $criteres11="";
             $criteres2="";
             $criteres="";
 ####Les conditions a appliquer les requetes
 if(!empty($PROVINCE_ID)){
    $criteres1.=" AND ben_beneficiaire.PROVINCE_ID=".$PROVINCE_ID;
             
     }    
###NB:Pour chaque rapport,avant d'ajouter un autre rapport,vous devez toujours terminer le precedent en mettant aussi son script apres le foreach et la serie
 #####requete pour le rapport des demandeurs par domaine
$ben_domaine=$this->Model->getRequete('SELECT ben_domaine.DESC_DOMAINE AS NAME,ben_domaine.DOMAINE_ID AS ID,COUNT(process_demande.ID_DEMANDE) AS NBRE FROM ben_domaine LEFT JOIN process_demande ON process_demande.DOMAINE_ID=ben_domaine.DOMAINE_ID JOIN ben_beneficiaire ON ben_beneficiaire.BENEFICIAIRE_ID=process_demande.BENEFICIAIRE_ID WHERE process_demande.PILIER_ID=3 '.$cond.' '.$criteres.' '.$criteres1.' '.$criteres11.'  GROUP BY  NAME,ID ORDER BY NBRE DESC');
 ####un count pour faciliter le calcul des pourcentages
$count_domaine=$this->Model->getRequeteOne('SELECT COUNT(process_demande.ID_DEMANDE) AS NBRE FROM ben_domaine LEFT JOIN process_demande ON process_demande.DOMAINE_ID=ben_domaine.DOMAINE_ID JOIN ben_beneficiaire ON ben_beneficiaire.BENEFICIAIRE_ID=process_demande.BENEFICIAIRE_ID WHERE process_demande.PILIER_ID=3   '.$cond.' '.$criteres.' '.$criteres1.' '.$criteres11.'');
  ##foreach et serie pour le rapport des demandeurs par domaine
           $total_domaines=0;
           $donnees_domaines='';
           $categorie_domaine="";
        foreach ($ben_demaine as $value){
            $color=$this->getcolor();
            $categorie_domaine.="'";
            $nb = (!empty($value['ID'])) ? $value['ID'] : "0" ;
            $somme1=($value['NBRE']>0) ? $value['NBRE'] : "0" ;
            $total_type=$total_type+$value['NBRE'];

            $pourcent=0;
            if ($tdemaine['NBRE']>0){
            $pourcent=($value['NBRE']/$count_domaine['NBRE'])*100;
               }
            $pourcentage=number_format($pourcent,2,',',' ');
            $name = (!empty($value['NAME'])) ? $value['NAME'] : "Autres" ;
            $rappel=str_replace("'", "\'", $name);
             $categorie_domaine.= $rappel."',";
            $donnees_type.="{name:'".str_replace("'","\'", $name)." :".$pourcentage." %', y:".$pourcent.",color:'".$color."',key2:1,key:'". $nb."'},";

            }

   ####script du rapport
   ###La variable qui stocke le script doit avoir le nom qui a un sens
 
 $rapport_domaine="<script type=\"text/javascript\">
  
Highcharts.chart('container1', {
 chart: {
        type: 'bar'
    },

title: {
        text: '<b> ".$titre." par domaine</b>'
    },
subtitle: {
        text: '<b> Le  ".date("d/m/Y")."<br> Total</b>:".$total_type."</b>'
     },

xAxis: {
        categories: [".$categorie_domaine."],
        crosshair: true
       },

yAxis: {
        allowDecimals: false,
        min: 0,
        title: {
            text: ''
           }
       },

tooltip: {
        formatter: function () {
            return '<b>' + this.x + '</b><br/>' +
                this.series.name + ': ' + this.y + '<br/>' +
                'Total: ' + this.point.stackTotal;
          }
        },

tooltip: {
        shared: true
        },
plotOptions: {
bar: {
cursor:'pointer',
point:{
events: {
             click: function(){  
             $(\"#titre\").html(\"LISTE DES DEMANDES \");
                                  
             $(\"#myModal\").modal();
                                 
             var row_count ='1000000';
             $(\"#mytable\").DataTable({
             \"processing\":true,
                        \"serverSide\":true,
                        \"bDestroy\": true,
                        \"oreder\":[],
                        \"ajax\":{
                            url:\"".base_url('index.php/dashboard/Dashboard_Demande_Stage/detail_type')."\",
                            type:\"POST\",
                            data:{
                                key:this.key,
                                PROVINCE_ID:$('#PROVINCE_ID').val(),
                                COMMUNE_ID:$('#COMMUNE_ID').val(),
                                ZONE_ID:$('#ZONE_ID').val(),
                                COLLINE_ID:$('#COLLINE_ID').val(),
                                STATUT:$('#STATUT').val(),
                                FORMATION_ID:$('#FORMATION_ID').val(),
                                mois:$('#mois').val(),
                                TRIMESTRE:$('#TRIMESTRE').val(),
                                SELECTIO:$('#SELECTIO').val(),
                               }
                               },
                        lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
                    pageLength: 10,
                            \"columnDefs\":[{
                             \"targets\":[],
                             \"orderable\":false
                               }],

                         dom: 'Bfrtlip',
                         buttons: ['copy', 'csv', 'excel', 'pdf', 'print'
                                                     ],
                       language: {
                                \"sProcessing\":     \"Traitement en cours...\",
                                \"sSearch\":         \"Rechercher&nbsp;:\",
                                \"sLengthMenu\":     \"Afficher _MENU_ &eacute;l&eacute;ments\",
                                \"sInfo\":           \"Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments\",
                                \"sInfoEmpty\":      \"Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment\",
                                \"sInfoFiltered\":   \"(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)\",
                                \"sInfoPostFix\":    \"\",
                                \"sLoadingRecords\": \"Chargement en cours...\",
                                \"sZeroRecords\":    \"Aucun &eacute;l&eacute;ment &agrave; afficher\",
                                \"sEmptyTable\":     \"Aucune donn&eacute;e disponible dans le tableau\",
                                \"oPaginate\": {
                                  \"sFirst\":      \"Premier\",
                                  \"sPrevious\":   \"Pr&eacute;c&eacute;dent\",
                                  \"sNext\":       \"Suivant\",
                                  \"sLast\":       \"Dernier\"
                                },
                                \"oAria\": {
                                  \"sSortAscending\":  \": activer pour trier la colonne par ordre croissant\",
                                  \"sSortDescending\": \": activer pour trier la colonne par ordre d&eacute;croissant\"
                                }
                            }
                              
                    });

                              }
                           }
                        },
           dataLabels: {
              enabled: true,
               format: '{point.y:,.2f} % '
            },
            showInLegend: true
        }
    }, 
  credits: {
              enabled: true,
              href: \"\",
              text: \"Mediabox\"
      },
   

    yAxis: {
        title: {
            text: ' '
        }
    },

    series: [{
         name: '".$titre."',
        color: 'green',
        data: [".$donnees_type."]
    }]

});
 </script>";

   #####le 2eme rapport
   ####requete
   ####foreach et serie
   ####script


   ####les filtres qui dependent des autres


    #####envoi des donnees
echo json_encode(array('rapp'=>$rapp));

}



 ####les details sur les rapports

 ####NB :chaque nom de la fonnction doit avoir un sens 

 #####exemple:

public function detail_domaine(){

}



}




