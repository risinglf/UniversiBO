{foreach from=$showNews_newsList item=showNews_notizia}
{include file=News/news.tpl id_notizia=$showNews_notizia.id_notizia titolo=$showNews_notizia.titolo notizia=$showNews_notizia.notizia autore=$showNews_notizia.autore autore_link=$showNews_notizia.autore_link id_autore=$showNews_notizia.id_autore data=$showNews_notizia.data modifica=$showNews_notizia.modifica modifica_link=$showNews_notizia.modifica_link elimina=$showNews_notizia.elimina elimina_link=$showNews_notizia.elimina_link nuova='false' scadenza=$showNews_notizia.scadenza }
&nbsp;<br />
{/foreach}