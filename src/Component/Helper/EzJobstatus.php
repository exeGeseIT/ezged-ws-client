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
 * Description of EzRow
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
class EzJobstatus extends EzJob implements JobstatusInterface
{
    /**
     * Le job est prêt et en attente d'être traité par le serveur de travaux
     */
    const STATUS_PENDING = 'PENDING';

    /**
     * Le job est en file, i.e sélectionné par le serveur de travaux pour s'exécuter
     */
    const STATUS_QUEUED = 'QUEUED';

    /**
     * Le job est terminé. Il sera purgé automatiquement
     */
    const STATUS_ENDED_PURGEABLE = 'PURGEABLE';

    /**
     * Le job est terminé. Il ne sera pas purgé automatiquement
     */
    const STATUS_ENDED_NOT_PURGEABLE = 'NOTPURGEABLE';

    /**
     * Le job est en erreur. Des tentatives d'exécution seront faites périodiquement.
     * (par défaut toutes les 3 mins)
     */
    const STATUS_ERROR = 'ERROR';

    /**
     * Le job a rencontré une erreur critique.
     *   => il est désactivé ==>> pas de ré-exécution périodique.
     * Ce status est également utilisé pour mettre un job en attente de l'indexation complète de son document (Centre d'indexation)
     */
    const STATUS_CRITICAL = 'CRITICAL';


    /**
     *  {
     *      "type": "generic",
     *      "JOBQUEUE_PURGEABLE": 1,
     *      "JOBQUEUE_RUNMISSED": 1,
     *      "JOBQUEUE_RUNONCE": 1,
     *      "JOBQUEUE_PRIORITY": 1,
     *      "JOBQUEUE_ID": 2439,
     *      "JOBQUEUE_STARTTIME": null,
     *      "JOBQUEUE_CRON": null,
     *      "JOBQUEUE_STEP": 1,
     *      "JOBQUEUE_ACTIVE": 1,
     *      "JOBQUEUE_WEIGHT": 100,
     *      "JOBQUEUE_TSTAMP": null,
     *      "JOBQUEUE_JOBTPLID": 51,
     *      "JOBQUEUE_SUPERJOB": 0,
     *      "JOBQUEUE_VALEND": 0,
     *      "JOBQUEUE_TYPE": "Interface",
     *      "JOBQUEUE_STATUS": 0,
     *      "JOBQUEUE_RUNATSTARTUP": 0,
     *      "JOBQUEUE_SECUSRID": 15
     *  }
     */
    public function __construct()
    {
        parent::__construct();
        $this->setProperties( ['JOBQUEUE_ACTIVE', 'JOBQUEUE_STEP'] )
             ->setProperty('JOBQUEUE_STATUS', self::JOBSTATUS_PENDING);
    }

    /**
     *
     * @return string|null
     */
    public function getStatus()
    {
        $status = null;
        switch ( $this->JOBQUEUE_STATUS ) {
            case self::JOBSTATUS_PENDING:
                $status = self::STATUS_PENDING;
                break;
            case self::JOBSTATUS_QUEUED:
                $status = self::STATUS_QUEUED;
                break;
            case self::JOBSTATUS_ENDED_PURGEABLE:
                $status = self::STATUS_ENDED_PURGEABLE;
                break;
            case self::JOBSTATUS_ENDED_NOT_PURGEABLE:
                $status = self::STATUS_ENDED_NOT_PURGEABLE;
                break;
            case self::JOBSTATUS_ERROR:
                $status = self::STATUS_ERROR;
                break;
            case self::JOBSTATUS_CRITICAL:
                $status = self::STATUS_CRITICAL;
                break;
        }
        return $status;
    }

    /**
     *
     * @return bool
     */
    public function isSucceed()
    {
        return in_array($this->getStatus(),[self::STATUS_ENDED_PURGEABLE, self::STATUS_ENDED_NOT_PURGEABLE]);
    }

    /**
     *
     * @return bool
     */
    public function isFailed()
    {
        return in_array($this->getStatus(),[self::STATUS_ERROR, self::STATUS_CRITICAL]);
    }

    /**
     *
     * @return bool
     */
    public function onFinalState()
    {
        //return ($this->isFailed() || $this->isSucceed());
        $status = $this->getStatus();
        return (null !== $status && !in_array($status,[self::STATUS_PENDING, self::STATUS_QUEUED]));
    }

}
