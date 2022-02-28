<?php

namespace ExeGeseIT\EzGEDWsClient\Core;

/**
 *
 * @author Jean-Claude GLOMBARD <jc.glombard@gmail.com>
 */
interface EzJobstatusInterface
{
    /**
     * The job is ready and waiting to be processed by the job server 
     */
    const JOBSTATUS_PENDING = 0;

    /**
     * The job is queued, eg. selected by the job server to run
     */
    const JOBSTATUS_QUEUED = 1;

    /**
     * The job is done. It will be purged automatically
     */
    const JOBSTATUS_ENDED_PURGEABLE = 2;

    /**
     * The job is done. It will not be purged automatically
     */
    const JOBSTATUS_ENDED_NOT_PURGEABLE = 3;

    /**
     * The job is in error. Performance attempts will be made periodically.
     * (by default every 3 mins)
     */
    const JOBSTATUS_ERROR = 4;

    /**
     * The job encountered a critical error.
     * => it is disabled ==>> no periodic re-execution.
     * This status is also used to put a job on hold for the complete indexing of its document (Indexing Center)
     */
    const JOBSTATUS_CRITICAL = 5;


    /**
     *
     * @return string|null
     */
    public function getStatus();

}
