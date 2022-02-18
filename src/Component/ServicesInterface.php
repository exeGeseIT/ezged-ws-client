<?php

/*
 * The MIT License
 *
 * Copyright 2019 Jean-Claude GLOMBARD <jc.glombard@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace ExeGeseIT\EzGEDWsClient\Component;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface ServicesInterface
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


    public static function initServices();

}
