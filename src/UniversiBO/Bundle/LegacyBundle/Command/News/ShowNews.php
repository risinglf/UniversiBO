<?php
namespace UniversiBO\Bundle\LegacyBundle\Command\News;

use \NewsItem;

use UniversiBO\Bundle\LegacyBundle\Framework\PluginCommand;

require_once ('News/NewsItem'.PHP_EXTENSION);

/**
 * ShowNews � un'implementazione di PluginCommand.
 *
 * Mostra la notizia $id_notizia.
 * Il BaseCommand che chiama questo plugin deve essere un'implementazione di CanaleCommand.
 * Nel paramentro di ingresso del deve essere specificato il numero di notizie da visualizzare.
 *
 * @package universibo
 * @subpackage News
 * @version 2.0.0
 * @author Fabrizio Pinto
 * @license GPL, {@link http://www.opensource.org/licenses/gpl-license.php}
 */
 
class ShowNews extends PluginCommand {
	
	
	/**
	 * Esegue il plugin
	 *
	 * @param array $param deve contenere: 
	 *  un array di id notizie da visualizzare
	 *	  es: array('id_notizia'=>5) 
	 */
	function execute($param)
	{
		
		$elenco_id_news		=  $param['id_notizie'];
		$flag_chkDiritti	=  $param['chk_diritti'];
//		var_dump($param['id_notizie']);
//		die();
		
		$bc        = $this->getBaseCommand();
		$canale    = $bc->getRequestCanale();
		$user      = $bc->getSessionUser();
		$fc        = $bc->getFrontController();
		$template  = $fc->getTemplateEngine();
		$krono     = $fc->getKrono();
		$user_ruoli = $user->getRuoli();

		if($flag_chkDiritti)
		{
			$id_canale = $canale->getIdCanale();
			$titolo_canale =  $canale->getTitolo();
			$ultima_modifica_canale =  $canale->getUltimaModifica();
			$canale    = $bc->getRequestCanale();
		}

		$personalizza_not_admin = false;

		$template->assign('showNews_addNewsFlag', 'false');
		if ($flag_chkDiritti && (array_key_exists($id_canale, $user_ruoli) || $user->isAdmin()))
		{
			$personalizza = true;
			
			if (array_key_exists($id_canale, $user_ruoli))
			{
				$ruolo = $user_ruoli[$id_canale];
				
				$personalizza_not_admin = true;
				$referente      = $ruolo->isReferente();
				$moderatore     = $ruolo->isModeratore();
				$ultimo_accesso = $ruolo->getUltimoAccesso();
			}
			
//			if ( $user->isAdmin() || $referente || $moderatore )
//			{
//				$template->assign('showNews_addNewsFlag', 'true');
//				$template->assign('showNews_addNews', 'Scrivi nuova notizia');
//				$template->assign('showNews_addNewsUri', 'index.php?do=NewsAdd&id_canale='.$id_canale);
//			}
		}
		else
		{
			$personalizza   = false;
			$referente      = false;
			$moderatore     = false;
			$ultimo_accesso = $user->getUltimoLogin();
		}
/*		
		$canale_news = $this->getNumNewsCanale($id_canale);

		$template->assign('showNews_desc', 'Mostra le ultime '.$num_news.' notizie del canale '.$id_canale.' - '.$titolo_canale);
*/
//		var_dump($elenco_id_news);
//		die();
		$canale_news = count($elenco_id_news);
//		var_dump($elenco_id_news);
//		die();
		if ( $canale_news == 0 )
		{
			$template->assign('showNews_langNewsAvailable', 'Non ci sono notizie da visualizzare');
			$template->assign('showNews_langNewsAvailableFlag', 'false');
		}
		else
		{
			$template->assign('showNews_langNewsAvailable', 'Ci sono '.$canale_news.' notizie');
			$template->assign('showNews_langNewsAvailableFlag', 'true');
		}
		
		//var_dump($elenco_id_news);
		$elenco_news = NewsItem::selectNewsItems($elenco_id_news);
		
		$elenco_news_tpl = array();

		if ($elenco_news ==! false )
		{
			
			$ret_news = count($elenco_news);

			for ($i = 0; $i < $ret_news; $i++)
			{
				$news = $elenco_news[$i];
				//var_dump($news);
				$this_moderatore = ($user->isAdmin() || ($moderatore && $news->getIdUtente()==$user->getIdUser()));
				
				$elenco_news_tpl[$i]['titolo']       = $news->getTitolo();
				$elenco_news_tpl[$i]['notizia']      = $news->getNotizia();
				$elenco_news_tpl[$i]['data']         = $krono->k_date('%j/%m/%Y', $news->getDataIns());
				//echo $personalizza,"-" ,$ultimo_accesso,"-", $news->getUltimaModifica()," -- ";
				$elenco_news_tpl[$i]['nuova']        = ($flag_chkDiritti && $personalizza_not_admin && $ultimo_accesso < $news->getUltimaModifica()) ? 'true' : 'false'; 
				$elenco_news_tpl[$i]['autore']       = $news->getUsername();
				$elenco_news_tpl[$i]['autore_link']  = 'ShowUser&id_utente='.$news->getIdUtente();
				$elenco_news_tpl[$i]['id_autore']    = $news->getIdUtente();
				
				$elenco_news_tpl[$i]['scadenza']     = '';
				if ( ($news->getDataScadenza()!=NULL) && ( $user->isAdmin() || $referente || $this_moderatore ) && $flag_chkDiritti)
				{
					$elenco_news_tpl[$i]['scadenza'] = 'Scade il '.$krono->k_date('%j/%m/%Y - %H:%i', $news->getDataScadenza() );
				}
				
				$elenco_news_tpl[$i]['modifica']     = '';
				$elenco_news_tpl[$i]['modifica_link']= '';
				$elenco_news_tpl[$i]['elimina']      = '';
				$elenco_news_tpl[$i]['elimina_link'] = '';
				if ( ($user->isAdmin() || $referente || $this_moderatore)  && $flag_chkDiritti)
				{
					$elenco_news_tpl[$i]['modifica']     = 'Modifica';
					$elenco_news_tpl[$i]['modifica_link']= 'NewsEdit&id_news='.$news->getIdNotizia();
					$elenco_news_tpl[$i]['elimina']      = 'Elimina';
					$elenco_news_tpl[$i]['elimina_link'] = 'NewsDelete&id_news='.$news->getIdNotizia().'&id_canale='.$id_canale;
				}

			}
		
		}
		
		$template->assign('showNews_newsList', $elenco_news_tpl);

		
	}
}