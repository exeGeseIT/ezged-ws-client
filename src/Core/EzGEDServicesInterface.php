<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface EzGEDServicesInterface
{
    /**
     * Ouvrir une session (Authent)
     */
    const REQ_AUTH = 'sec/authenticate';

    /**
     * Maintenir la connexion
     */
    const REQ_AUTH_KEEPALIVE = 'secses/keepalive';

    /**
     * Fermer une session (Logout)
     */
    const REQ_LOGOUT = 'secses/delete';

    /**
     * Connaitre le status d'un job
     */
    const REQ_DOWNLOAD_FILE = 'showdocs';

    /**
     * Upload d'un Fichier
     */
    const REQ_UPLOAD = 'upload';

    /**
     * Lister les vues/requêtes disponibles pour le compte utilisé
     */
    const REQ_GET_PERIMETER = 'query/gettreearchive';

    /**
     * Obtenir toutes les lignes retournées par une requête.
     */
    const REQ_EXEC_REQUEST = 'query/getexec';

    /**
     * Obtenir toutes les lignes retournées par une requête.
     * @todo implémentation
     */
    const REQ_EXEC_GLOBAL_SEARCH = 'query/globalsearch_v3';

    /**
     * Voir la liste des fichiers (image) d'un enregistrement
     */
    const REQ_GET_RECORD_FILES = 'docpak/loadalllastrevision';

    /**
     * Créer un enregistrement
     */
    const REQ_CREATE_RECORD = 'doctbl/insertrow';

    /**
     * Mettre à jour un enregistrement
     */
    const REQ_UPDATE_RECORD = 'doctbl/updaterow';

    /**
     * Ajouter un fichier (image) à jour un enregistrement
     */
    const REQ_ADD_RECORD_FILE = 'docpak/addpages';

    /**
     * Connaitre le status d'un job
     */
    const REQ_GET_JOB_STATUS = 'jobqueue/load';

}
