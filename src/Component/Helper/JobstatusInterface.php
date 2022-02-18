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

namespace ExeGeseIT\EzGEDWsClient\Component\Helper;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface JobstatusInterface
{
    /**
     * Le travail est prêt et en attente d'être traité par le serveur de travaux
     */
    const JOBSTATUS_PENDING = 0;

    /**
     * Le travail est en file, i.e sélectionné par le serveur de travaux pour s'exécuter
     */
    const JOBSTATUS_QUEUED = 1;

    /**
     * Le travail est terminé. Il sera purgé automatiquement
     */
    const JOBSTATUS_ENDED_PURGEABLE = 2;

    /**
     * Le travail est terminé. Il ne sera pas purgé automatiquement
     */
    const JOBSTATUS_ENDED_NOT_PURGEABLE = 3;

    /**
     * Le travail est en erreur. Des tentatives d'exécution seront faites périodiquement.
     * (par défaut toutes les 3 mins)
     */
    const JOBSTATUS_ERROR = 4;

    /**
     * Le travail a rencontré une erreur critique.
     *   => il est désactivé ==>> pas de ré-exécution périodique.
     * Ce status est également utilisé pour mettre un travail en attente de l'indexation complète de son document (Centre d'indexation)
     */
    const JOBSTATUS_CRITICAL = 5;


    /**
     *
     * @return string|null
     */
    public function getStatus();

}
