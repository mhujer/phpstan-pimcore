<?php

declare(strict_types=1);

namespace WorkflowNames;

use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\WorkflowInterface;

function check(Registry $registry, WorkflowInterface $workflow, Marking $marking, object $subject, string $dynamic): void
{
    $registry->get($subject, 'missing');
    $registry->has(workflowName: 'missing', subject: $subject);
    $workflow->apply($subject, 'missing');
    $workflow->can(transitionName: 'missing', subject: $subject);
    $marking->has(place: 'missing');
    $registry->get($subject, 'product_editorial');
    $registry->has($subject, 'translation_job_default');
    $workflow->can($subject, 'translate');
    $marking->has('translated');
    $workflow->apply($subject, 'submit');
    $workflow->can($subject, 'publish');
    $marking->has('draft');
    $marking->has('review');
    $marking->has('published');
    $registry->get($subject);
    $registry->has($subject, null);
    $registry->get($subject, $dynamic);
    $workflow->can($subject, $dynamic);
    $marking->has($dynamic);
    $workflow->can(...);
}
