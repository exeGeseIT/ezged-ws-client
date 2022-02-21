<?php

namespace ExeGeseIT\EzGEDWsClient\Core\Dto;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface EzJobstatusInterface
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
