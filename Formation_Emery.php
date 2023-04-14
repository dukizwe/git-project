

  <?php 
  ##rapport d' evolution des demandes 
  ## fait par Emery NIYONGABO emery@mediabox.bi
  ## le 04/04/2023 
  defined('BASEPATH') OR exit('No direct script access allowed');

  class Evolution_Demandes_New extends MY_Controller {
    public function __construct() 
       {
      parent::__construct();    
      $this->load->helper('form');    
      $this->load->library('table');   
      $this->load->library('form_validation');   
      }
    //Appel des filtres qui ne dependent pas des autres dans la fonction index
    public function index()
         {
      $data['title'] = "Evolution des demandes";    
      $data['current_document_id'] = NULL;

      $data['doc_satatut']=$this->My_model->getRequete('SELECT * FROM `doc_document_statut` WHERE 1 AND statut_id IN(1,2,3,9,14,16,19,20) ORDER BY statut_descr ASC');
    $data['annees'] =$this->My_model->getRequete('SELECT DISTINCT date_format(doc_document_historique.`datecreation`, "%Y") as ANN from doc_document_historique where datecreation is not null ORDER BY ANN DESC');
    
    $data['doc_members']=$this->My_model->getRequete('SELECT `mbr_id`,`mbr_fname`,`mbr_lname` FROM `members` WHERE IS_ACTIVE=1 AND members.rol_id IN (13,14,16,12,8,2,15)');
         ###appel de la vue
      $this->page = 'Evolution_Demandes_New_View';
      $this->layout($data);
        }
      //Fonction get_rapport et Appel des filtres qui dependent des autres  
    public function get_rapport(){
      #### declaration des variable
      $ANNEES=$this->input->post('ANNEES');
      $PERIODE=$this->input->post('PERIODE');
      $statut_id=$this->input->post('statut_id');
      $mbr_id=$this->input->post('mbr_id');
      $criteres_date="";
      $criteres_statut="";
      $critere_agent="";
      ### condition pour les filtres
       if (!empty($mbr_id)) {
       $critere_agent=" AND membre=".$mbr_id;
          }
      if (!empty($statut_id)) {
        $criteres_statut=" AND doc.statut_id=".$statut_id;
         }
      $critaire="";
      $DateRApport="";
      if(!empty($ANNEES))
         {
       $critaire.=" AND date_format(doc.datecreation,'%Y')= '".$ANNEES."'";
       $DateRApport="Le ".date('Y',strtotime($ANNEES))."";       
          } 
       if(!empty($PERIODE))
          {
       $critaire.=" AND date_format(doc.datecreation,'%Y-%m')= '".$PERIODE."'";
       $DateRApport="Le ".date('m/Y',strtotime($PERIODE)).""; 
          }

        $mois=$this->My_model->getRequete('SELECT DISTINCT DATE_FORMAT(doc_document.datecreation, "%Y-%m") as mois from doc_document where DATE_FORMAT(doc_document.datecreation, "%Y")="'.$ANNEES.'" ORDER BY mois DESC');
        $mois_select="<option selected='' disabled=''>séléctionner</option>";
           foreach ($mois as $value)
             {
          if ($PERIODE==$value['mois'])
              { 
          $mois_select.="<option value='".$value['mois']."' selected>".$value['mois']."</option>";
             }else{ 
          $mois_select.="<option value='".$value['mois']."'>".$value['mois']."</option>";
           } 
          }
         
        $total_fonc=0;
        $nb_fonc="";
        $nbr_fonc="";
        $color="";
        $categorie_fonc='';
         ### requette pour les rapports
   $rdv=$this->Model->getRequete("SELECT DISTINCT  DATE_FORMAT(doc.`datecreation`,'%Y-%m-%d') as dat FROM doc_document as doc WHERE 1 ".$critaire." ".$criteres_statut." ORDER BY dat ASC");
      $req_statut=$this->Model->getRequete("SELECT typ.type_document_voyage_id,typ.document_nom_fr FROM type_document_voyage as typ");
        foreach ($req_statut as $key_fonc) {
        $tot_f=0;
        $donne_fonc='';
        foreach ($rdv as  $value_fonc) {
            # code...
            $categorie_fonc.="'".$value_fonc['dat']."',";
         $nbr_fonc_req=$this->Model->getRequeteOne("SELECT COUNT(doc.id) nbr FROM doc_document doc  join doc_document_historique  ON doc_document_historique.requete=doc.id   WHERE  doc.type_document_voyage_id=".$key_fonc['type_document_voyage_id']." ".$critaire." ".$critere_agent." AND DATE_FORMAT(doc.datecreation,'%Y-%m-%d')='". $value_fonc['dat']."'");
            $tot_f+=$nbr_fonc_req['nbr'];
            $donne_fonc.="{ y:".$nbr_fonc_req['nbr'].",key:'".$value_fonc['dat']."', key1 :".$key_fonc['type_document_voyage_id']." },";
                 }
          $nbr_fonc="{
         name: '".$key_fonc['document_nom_fr']." "."(".number_format($tot_f,0,',',' ').")',
        data: [";
        $nbr_fonc.=$donne_fonc."] },";
        $nb_fonc.=$nbr_fonc;
        $total_fonc+=$tot_f;
           }
       $data['nb_fonc']=$nb_fonc;
       $data['tot_f']=number_format($total_fonc,0,',',' ');
       $data['categorie_fonc']=$categorie_fonc;

    

$rapp="<script type=\"text/javascript\">

  Highcharts.chart('container', {
    chart: {
        type: 'line',
        
          },

    title: {
        text: '<b> Evolution des demandes par document<br> Total = ".number_format($total_fonc,0,',',' ')." </b> <br>'
    },
    xAxis: {
        categories: [".$categorie_fonc."],
        labels: {
            skew3d: true,
            style: {
                fontSize: '16px'
            }
        }
    },
     credits: {
      enabled: true,
      href: \"\",
      text: \"Mediabox\"
    },

    yAxis: {
        allowDecimals: false,
        min: 0,
        title: {
            text: ' ',
            skew3d: true
        }
    },

    tooltip: {
        headerFormat: '<b>{point.key}</b><br>',
        pointFormat: '<span style=\"color:{series.color}\">\u25CF</span> {series.name}: {point.y} / {point.stackTotal}'
    },

    plotOptions: {
        line: {
      cursor:'pointer',
      depth: 25,
      point:{
        events: {
         click: function()
         {
                              // alert(this.key);
                              $(\"#titre\").html(\"Liste des demandes\");

                              $(\"#myModal\").modal();


                              var row_count =\"1000000\";
                              $(\"#mytable\").DataTable({
                                \"processing\":true,
                                \"serverSide\":true,
                                \"bDestroy\": true,
                                \"oreder\":[],
                                \"ajax\":{
                                 url:\"".base_url('rapport/Evolution_Demandes_New/detail_statut')."\",
                                  type:\"POST\",
                                  data:{
                                  key:this.key,
                                  key1:this.key1,
                                  ANNEES:$('#ANNEES').val(),
                                  PERIODE:$('#PERIODE').val(),
                                  statut_id:$('#statut_id').val(), 
                                  mbr_id:$('#mbr_id').val(),
                                  
                                   }
                                },
                                lengthMenu: [[10,50, 100, row_count], [10,50, 100, \"All\"]],
                                pageLength: 10,
                                \"columnDefs\":[{
                                  \"targets\":[],
                                  \"orderable\":false
                                }],

                                dom: 'Bfrtlip',
                                buttons: [
                                'excel', 'print','pdf'
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
                         enabled: true
                       },
                       showInLegend: true
                     }
                   },  
               series:
               [".$nb_fonc."]
                });

              </script>";

  

   echo json_encode(array('rapp'=>$rapp,'select_month'=>$mois_select));
     }
  // Details du rapport statut
  function detail_statut(){

    $ANNEES=$this->input->post('ANNEES');
    $PERIODE=$this->input->post('PERIODE');
    $statut_id=$this->input->post('statut_id');
    $mbr_id=$this->input->post('mbr_id');
    $KEY=$this->input->post('key');
    $KEY1=$this->input->post('key1');
    $criteres_date="";
    $criteres_statut="";
    if (!empty($statut_id)) {
      $criteres_statut=" AND d.statut_id=".$statut_id;
    }
    $critere_agent="";
    if (!empty($mbr_id)) {
      $critere_agent=" AND doc_document_historique.membre=".$mbr_id;
    }
    
    $date="";
    if(!empty($ANNEES))
      {
        $date.=" AND date_format(d.datecreation,'%Y')= '".$ANNEES."'";
      }
    if(!empty($PERIODE))
      {
        $date.=" AND date_format(d.datecreation,'%Y-%m')= '".$PERIODE."'  ";
      }

    $var_search =!empty($_POST['search']['value']) ? $_POST['search']['value'] : null;

    $query_principal="SELECT distinct(doc_document_historique.requete),`nom`,`prenom`,`cni_numero`,statut_descr,document_nom_fr,rdv_date_ancienne,d.rdv_date,doc_document_historique.datetraitement FROM doc_document d LEFT JOIN `doc_document_historique` ON d.id=doc_document_historique.requete LEFT JOIN doc_document_statut ON doc_document_historique.`statut_id`=doc_document_statut.`statut_id` LEFT JOIN type_document_voyage td on d.type_document_voyage_id=td.type_document_voyage_id WHERE 1 AND d.type_document_voyage_id=".$KEY1." ".$criteres_statut."".$date.$critere_agent."";

      $limit='LIMIT 0,10';

      if($_POST['length'] != -1)
      {
        $limit='LIMIT '.$_POST["start"].','.$_POST["length"];
      }
      $order_by='';
      if($_POST['order']['0']['column']!=0)
      {
        $order_by = isset($_POST['order']) ? ' ORDER BY '.$_POST['order']['0']['column'] .'  '.$_POST['order']['0']['dir'] : ' ORDER BY nom ASC';
      }
      $search = !empty($_POST['search']['value']) ? (" AND (nom LIKE '%$var_search%' OR prenom LIKE '%$var_search%' OR cni_numero LIKE '%$var_search%' OR rdv_date_ancienne LIKE '%$var_search%') ") : '';

      $critaire=" AND DATE_FORMAT(d.`datecreation`,'%Y-%m-%d')='".$KEY."'";

      $query_secondaire=$query_principal.'  '.$critaire.'  '.$search.' '.$order_by.'   '.$limit;
      $query_filter=$query_principal.'  '.$critaire.'  '.$search;
      
      $fetch_data = $this->My_model->datatable($query_secondaire);
      $u=0;
      $data = array();
      foreach ($fetch_data as $row)  
      {
        $u++;
        $demandeur=array();
        $demandeur[] ='<center><font color="#000000" size=2><label>'.$row->nom.'</label></font> </center>';
        $demandeur[] ='<center><font color="#000000" size=2><label>'.$row->prenom.'</label></font> </center>';
         $demandeur[] ='<center><font color="#000000" size=2><label>'.$row->cni_numero.'</label></font> </center>';
        $demandeur[] ='<center><font color="#000000" size=2><label>'.$row->document_nom_fr.'</label></font> </center>';
         
        $demandeur[] ='<center><font color="#000000" size=2><label>'.($row->rdv_date)?date('d-m-Y H:i',strtotime($row->rdv_date)):'N/A'.'</label></font> </center>';
        $demandeur[] ='<center><font color="#000000" size=2><label>'.($row->rdv_date)?date('d-m-Y H:i',strtotime($row->rdv_date_ancienne)):'N/A'.'</label></font> </center>';

        $demandeur[] ='<center><font color="#000000" size=2><label>'.($row->rdv_date)?date('d-m-Y H:i',strtotime($row->datetraitement)):'N/A'.'</label></font> </center>';
       
        $data[] = $demandeur;
      }
      $output = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" =>$this->My_model->all_data($query_principal),
        "recordsFiltered" => $this->My_model->filtrer($query_filter),
        "data" => $data
      );
      echo json_encode($output);
    }
  }