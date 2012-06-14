<?php
namespace Universibo\Bundle\LegacyBundle\Entity\Files;

use Doctrine\DBAL\Connection;

use Universibo\Bundle\LegacyBundle\Entity\DoctrineRepository;

use Universibo\Bundle\LegacyBundle\Entity\CanaleRepository;
use Universibo\Bundle\LegacyBundle\Entity\UserRepository;

/**
 * DBNewsItem repository
 *
 * @author Davide Bellettini <davide.bellettini@gmail.com>
 * @license GPL v2 or later
 */
class FileItemRepository extends DoctrineRepository
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var CanaleRepository
     */
    private $channelRepository;

    public function __construct(Connection $db, UserRepository $userRepository, CanaleRepository $channelRepository, $convert = false)
    {
        parent::__construct($db);

        $this->userRepository = $userRepository;
        $this->channelRepository = $channelRepository;
    }

    public function findByChannel($channelId)
    {
        $ids = $this->findIdByChannel($channelId);

        return is_array($ids) ? $this->findManyById($ids) : $ids;
    }

    public function countByChannel($channelId)
    {
        $db = $this->getConnection();

        $query = 'SELECT count(A.id_file) FROM file A, file_canale B
        WHERE A.id_file = B.id_file AND eliminato!='
        . $db->quote(FileItem::ELIMINATO) . 'AND B.id_canale = '
        . $db->quote($id_canale) . '';
        
        $stmt = $db->executeQuery($query);
        
        return $stmt->fetchColumn();
    }

    public function findLatestByChannels(array $channelIds, $limit)
    {
        if (count($channelIds) === 0) {
            return array();
        }

        $db = $this->getConnection();
        array_walk($channelIds, array($db, 'quote'));

        $values = implode(',', $channelIds);
        
        $builder = $db->createQueryBuilder();
        
        $stmt = $builder
            ->select('f.id_file')
            ->from('file', 'f')
            ->from('file_canale', 'fc')
            ->where('f.id_file = fc.id_file')
            ->andWhere('f.eliminato = ?')
            ->andWhere('fc.id_canale IN ?')
            ->setParameters(array('N', $channelIds), array(null, Connection::PARAM_INT_ARRAY))
            ->orderBy('f.data_inserimento', 'DESC')
            ->execute();
        

        $ids = array();

        while (false !== ($row = $stmt->fetch())) {
            $ids[] = $row[0];
        }

        return $this->findManyById($ids);
    }

    public function findIdByChannel($channelId)
    {
        $db = $this->getConnection();

        $query = 'SELECT A.id_file  FROM file A, file_canale B
        WHERE A.id_file = B.id_file AND eliminato='
                . $db->quote(FileItem::NOT_ELIMINATO) . ' AND B.id_canale = '
                . $db->quote($channelId) . ' AND A.data_inserimento < '
                . $db->quote(time())
                . 'ORDER BY A.id_categoria, A.data_inserimento DESC';
        $stmt = $db->executeQuery($query);

        $id_file_list = array();

        while (false !== ($row = $stmt->fetch())) {
            $id_file_list[] = $row[0];
        }

        

        return $id_file_list;
    }

    public function findManyById(array $ids)
    {
        $db = $this->getConnection();

        if (count($ids) == 0) {
            return array();
        }

        //esegue $db->quote() su ogni elemento dell'array
        array_walk($ids, array($db, 'quote'));
        if (count($ids) == 1)
            $values = $ids[0];
        else
            $values = implode(',', $ids);

        //		$query = 'SELECT id_file, permessi_download, permessi_visualizza, A.id_utente, titolo,
        //						 A.descrizione, data_inserimento, data_modifica, dimensione, download,
        //						 nome_file, A.id_categoria, id_tipo_file, hash_file, A.password,
        //						 username, C.descrizione, D.descrizione, D.icona, D.info_aggiuntive
        //						 FROM file A, utente B, file_categoria C, file_tipo D
        //						 WHERE A.id_utente = B.id_utente AND A.id_categoria = C.id_file_categoria AND id_tipo_file = D.id_file_tipo AND A.id_file  IN ('.$values.') AND eliminato!='.$db->quote(FILE_ELIMINATO);
        $query = 'SELECT id_file, permessi_download, permessi_visualizza, A.id_utente, titolo,
        A.descrizione, data_inserimento, data_modifica, dimensione, download,
        nome_file, A.id_categoria, id_tipo_file, hash_file, A.password,
        C.descrizione, D.descrizione, D.icona, D.info_aggiuntive
        FROM file A, file_categoria C, file_tipo D
        WHERE A.id_categoria = C.id_file_categoria AND id_tipo_file = D.id_file_tipo AND A.id_file  IN ('
                . $values . ') AND eliminato=' . $db->quote(FileItem::NOT_ELIMINATO);

        $query .= ' ORDER BY C.id_file_categoria, data_inserimento DESC';

        $stmt = $db->executeQuery($query);

        $rows = $stmt->rowCount();

        if ($rows == 0)
            return false;
        $files_list = array();

        $userRepo = $this->userRepository;

        while (false !== ($row = $stmt->fetch())) {
            $username = $userRepo->getUsernameFromId($row[3]);

            $files_list[] = new FileItem($row[0], $row[1], $row[2], $row[3],
                    $row[4], $row[5], $row[6], $row[7], $row[8], $row[9],
                    $row[10], $row[11], $row[12], $row[13], $row[14],
                    $username, $row[15], $row[16], $row[17], $row[18]);
        }

        

        return $files_list;
    }

    public function findAll()
    {
        $db = $this->getConnection();

        //		$query = 'SELECT id_file, permessi_download, permessi_visualizza, A.id_utente, titolo,
        //						 A.descrizione, data_inserimento, data_modifica, dimensione, download,
        //						 nome_file, A.id_categoria, id_tipo_file, hash_file, A.password,
        //						 username, C.descrizione, D.descrizione, D.icona, D.info_aggiuntive
        //						 FROM file A, utente B, file_categoria C, file_tipo D
        //						 WHERE A.id_utente = B.id_utente AND A.id_categoria = C.id_file_categoria AND id_tipo_file = D.id_file_tipo AND A.id_file  IN ('.$values.') AND eliminato!='.$db->quote(FILE_ELIMINATO);
        $query = 'SELECT id_file, permessi_download, permessi_visualizza, A.id_utente, titolo,
        A.descrizione, data_inserimento, data_modifica, dimensione, download,
        nome_file, A.id_categoria, id_tipo_file, hash_file, A.password,
        C.descrizione, D.descrizione, D.icona, D.info_aggiuntive
        FROM file A, file_categoria C, file_tipo D
        WHERE A.id_categoria = C.id_file_categoria AND id_tipo_file = D.id_file_tipo AND eliminato=' . $db->quote(FileItem::NOT_ELIMINATO);

        $query .= ' ORDER BY C.id_file_categoria, data_inserimento DESC';

         $stmt = $db->executeQuery($query);

        $rows = $stmt->rowCount();

        if ($rows == 0)
            return false;
        
        $files_list = array();

        $userRepo = $this->userRepository;

        while (false !== ($row = $stmt->fetch())) {
            $username = $userRepo->getUsernameFromId($row[3]);

            $files_list[] = new FileItem($row[0], $row[1], $row[2], $row[3],
                    $row[4], $row[5], $row[6], $row[7], $row[8], $row[9],
                    $row[10], $row[11], $row[12], $row[13], $row[14],
                    $username, $row[15], $row[16], $row[17], $row[18]);
        }

        return $files_list;
    }

    public function getKeyworkds($fileId)
    {
        $db = $this->getConnection();

        $query = 'SELECT keyword FROM file_keywords WHERE id_file='.$db->quote($fileId);
        $stmt = $db->executeQuery($query);

        $elenco_keywords = array ();

        while (false !== ($row = $stmt->fetch())) {
            $elenco_keywords[] = $row[0];
        }

        return $elenco_keywords;
    }

    public function addKeyword($fileId, $keyword)
    {
        $db = $this->getConnection();
        $query = 'INSERT INTO file_keywords(id_file, keyword) VALUES ('.$db->quote($fileId).' , '.$db->quote($keyword) .');';
        $res =  $db->executeUpdate($query);
    }

    public function removeKeyword($fileId, $keyword)
    {
        $db = $this->getConnection();
        $query = 'DELETE FROM file_keywords WHERE id_file = '.$db->quote($fileId).' AND keyword = '.$db->quote($keyword);
        $res = $db->executeUpdate($query);
    }

    public function updateKeywords($fileId, array $keywords)
    {
        $old_elenco_keywords = $this->getKeyworkds($fileId);

        $db = $this->getConnection();
        ignore_user_abort(1);
        $db->beginTransaction();

        foreach ($keywords as $value) {
            if (!in_array($value, $old_elenco_keywords)) {
                $this->addKeyword($fileId, $value);
            }
        }

        foreach ($old_elenco_keywords as $value) {
            if (!in_array($value,$elenco_keywords)) {
                $this->removeKeyword($fileId, $value);
            }
        }

        $db->commit();

        ignore_user_abort(0);
    }

    public function updateDownload(FileItem $file)
    {
        $db = $this->getConnection();

        $query = 'UPDATE file SET download = ' . $db->quote($file->getDownLoad())
        . ' WHERE id_file = ' . $db->quote($file->getIdFile());
        
        return $db->executeUpdate($query) > 0;
    }

    public function getTypes()
    {
        $db = $this->getConnection();

        $query = 'SELECT id_file_tipo, descrizione FROM file_tipo';
        $stmt = $db->executeQuery($query);

        $tipi = array();

        while (false !== ($row = $stmt->fetch())) {
            $tipi[$row[0]] = $row[1];
        }

        return $tipi;
    }

    public function getTypeRegExps()
    {
        $db = $this->getConnection();

        $query = 'SELECT id_file_tipo, pattern_riconoscimento FROM file_tipo';
        $stmt = $db->executeQuery($query);

        $tipi = array();

        while (false !== ($row = $stmt->fetch())) {
            $tipi[$row[0]] = $row[1];
        }

        return $tipi;
    }

    public function getCategories()
    {
        $db = $this->getConnection();
        $query = 'SELECT id_file_categoria, descrizione FROM file_categoria';
        $stmt = $db->executeQuery($query);

        $categorie = array();

        while (false !== ($row = $stmt->fetch())) {
            $categorie[$row[0]] = $row[1];
        }

        return $categorie;
    }

    public function findByUserId($userId, $order = false)
    {
        $db = $this->getConnection();

        $query = 'SELECT id_file, permessi_download, permessi_visualizza, A.id_utente, titolo,
        A.descrizione, data_inserimento, data_modifica, dimensione, download,
        nome_file, A.id_categoria, id_tipo_file, hash_file, A.password,
        C.descrizione, D.descrizione, D.icona, D.info_aggiuntive
        FROM file A, file_categoria C, file_tipo D
        WHERE A.id_categoria = C.id_file_categoria AND id_tipo_file = D.id_file_tipo AND  eliminato ='
        . $db->quote(FileItem::NOT_ELIMINATO) . ' AND id_utente = '
        . $db->quote($userId)
        . ($order ? ' ORDER BY data_inserimento DESC' : '');

        $stmt = $db->executeQuery($query);

        $rows = $stmt->rowCount();

        if ($rows == 0)
            return false;
        $files_list = array();

        while (false !== ($row = $stmt->fetch())) {
            $username = $this->userRepository->getUsernameFromId($row[3]);
            $files_list[] = new FileItem($row[0], $row[1], $row[2], $row[3],
                    $row[4], $row[5], $row[6], $row[7], $row[8], $row[9],
                    $row[10], $row[11], $row[12], $row[13], $row[14],
                    $username, $row[15], $row[16], $row[17], $row[18]);
        }

        return $files_list;
    }

    public function insert(FileItem $file)
    {
        $db = $this->getConnection();

        $eliminata = FileItem::NOT_ELIMINATO;
        $query = 'INSERT INTO file (id_file, permessi_download, permessi_visualizza, id_utente, titolo,
        descrizione, data_inserimento, data_modifica, dimensione, download,
        nome_file, id_categoria, id_tipo_file, hash_file, password, eliminato) VALUES '
        . '( ' . $next_id . ' , '
        . $db->quote($file->getPermessiDownload()) . ' , '
        . $db->quote($file->getPermessiVisualizza()) . ' , '
        . $db->quote($file->getIdUtente()) . ' , '
        . $db->quote($file->getTitolo()) . ' , '
        . $db->quote($file->getDescrizione()) . ' , '
        . $db->quote($file->getDataInserimento()) . ' , '
        . $db->quote($file->getDataModifica()) . ' , '
        . $db->quote($file->getDimensione()) . ' , '
        . $db->quote($file->getDownload()) . ' , '
        . $db->quote($file->getRawNomeFile()) . ' , '
        . $db->quote($file->getIdCategoria()) . ' , '
        . $db->quote($file->getIdTipoFile()) . ' , '
        . $db->quote($file->getHashFile()) . ' , '
        . $db->quote($file->getPassword()) . ' , '
        . $db->quote(FileItem::NOT_ELIMINATO) . ' )';

        $res = $db->executeUpdate($query);

        $file->setIdFile($db->lastInsertId('file_id_file'));
        
        return true;
    }

    public function update(FileItem $file)
    {
        $db = $this->getConnection();

        //$scadenza = ($this->getDataScadenza() == NULL) ? ' NULL ' : $db->quote($this->getDataScadenza());
        //$flag_urgente = ($this->isUrgente()) ? NEWS_URGENTE : NEWS_NOT_URGENTE;
        //$deleted = ($this->isEliminata()) ? NEWS_ELIMINATA : NEWS_NOT_ELIMINATA;
        $query = 'UPDATE file SET id_file = ' . $db->quote($file->getIdFile())
        . ' , permessi_download = '
        . $db->quote($file->getPermessiDownload())
        . ' , permessi_visualizza = '
        . $db->quote($file->getPermessiVisualizza())
        . ' , id_utente = ' . $db->quote($file->getIdUtente())
        . ' , titolo = ' . $db->quote($file->getTitolo())
        . ' , descrizione = ' . $db->quote($file->getDescrizione())
        . ' , data_inserimento = '
        . $db->quote($file->getDataInserimento())
        . ' , data_modifica = ' . $db->quote($file->getDataModifica())
        . ' , dimensione = ' . $db->quote($file->getDimensione())
        . ' , download = ' . $db->quote($file->getDownload())
        . ' , nome_file = ' . $db->quote($file->getRawNomeFile())
        . ' , id_categoria = ' . $db->quote($file->getIdCategoria())
        . ' , id_tipo_file = ' . $db->quote($file->getIdTipoFile())
        . ' , hash_file = ' . $db->quote($file->getHashFile())
        . ' , password = ' . $db->quote($file->getPassword())
        . ' WHERE id_file = ' . $db->quote($file->getIdFile());
        //echo $query;
        $res = $db->executeUpdate($query);

        return true;
    }

    public function getChannelIds(FileItem $file)
    {
        $id_file = $file->getIdFile();

        $db = $this->getConnection();

        $where = 'WHERE id_file='. $db->quote($id_file);
        $query = 'SELECT id_canale FROM file_canale '.$where;
        $query .= 'UNION SELECT id_canale FROM file_studente_canale '.$where;

        $stmt = $db->executeQuery($query);

        $elenco_id_canale = array();

        while (false !== ($id = $stmt->fetchColumn())) {
            $elenco_id_canale[] = $id;
        }

        sort($elenco_id_canale);

        return $elenco_id_canale;
    }

    public function addToChannel(FileItem $file, $channelId)
    {
        $db = $this->getConnection();

        if (!$this->channelRepository->idExists($channelId)) {
            return false;
        }

        $query = 'INSERT INTO file_canale (id_file, id_canale) VALUES ('
        . $db->quote($file->getIdFile()) . ',' . $db->quote($channelId)
        . ')';

        $res = $db->executeUpdate($query);

        $ids = $file->getIdCanali();
        $ids[] = $channelId;
        $file->setIdCanali($ids);

        return true;
    }

    public function removeFromChannel(FileItem $file, $channelId)
    {
        $db = $this->getConnection();

        $query = 'DELETE FROM file_canale WHERE id_canale='
        . $db->quote($channelId) . ' AND id_file='
        . $db->quote($channelId->getIdFile());
        //? da testare il funzionamento di =
        $res = $db->executeUpdate($query);

        $file->setIdCanali($ids = array_diff($file->getIdCanali(),array($channelId)));
        if (count($ids) === 0) {
            $this->delete($file);
        }
    }

    public function delete(FileItem $file)
    {
        $lista_canali = $this->getChannelIds($file);

        if (count($lista_canali) == 0) {
            $db = $this->getConnection();

            $query = 'UPDATE file SET eliminato  = '
            . $db->quote(FileItem::ELIMINATO) . ' WHERE id_file = '
            . $db->quote($file->getIdFile());
            //echo $query;
            $res = $db->executeUpdate($query);

            return true;
        }

        return false;
    }
}