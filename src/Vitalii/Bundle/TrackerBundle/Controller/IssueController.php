<?php

namespace Vitalii\Bundle\TrackerBundle\Controller;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Vitalii\Bundle\TrackerBundle\Entity\Issue;

/**
 * @Route("/issue")
 */
class IssueController extends Controller
{
    /**
     * @Route("/", name="tracker.issue_index")
     * @Template
     * @Acl(
     *     id="tracker.issue_view",
     *     type="entity",
     *     class="VitaliiTrackerBundle:Issue",
     *     permission="VIEW"
     * )
     */
    public function indexAction()
    {
        return [
            'gridName' => 'issues-grid',
            'entity_class' => $this->container->getParameter('vitalii_tracker.issue.entity.class')
        ];
    }

    /**
     * @Route("/create", name="tracker.issue_create")
     * @Template("VitaliiTrackerBundle:Issue:update.html.twig")
     * @Acl(
     *     id="tracker.issue_create",
     *     type="entity",
     *     class="VitaliiTrackerBundle:Issue",
     *     permission="CREATE"
     * )
     */
    public function createAction(Request $request)
    {
        $issue = new Issue();
        $issue->setCode($this->get('tracker.issue.manager')->generateCode());
        $issue->setReporter($this->getUser());
        if ($request->get('_action') === 'assign') {
            $assignee = $this->getDoctrine()->getRepository('OroUserBundle:User')->find($request->get('entityId'));
            $issue->setAssignee($assignee);
        }

        return $this->update($issue);
    }

    /**
     * @Route("/subtask/{id}", name="tracker.issue_subtask")
     * @Template("VitaliiTrackerBundle:Issue:update.html.twig")
     * @Acl(
     *     id="tracker.issue_create",
     *     type="entity",
     *     class="VitaliiTrackerBundle:Issue",
     *     permission="CREATE"
     * )
     */
    public function addSubtaskAction(Issue $parent)
    {
        $this->denyAccessUnlessGranted(
            'subtask',
            $parent,
            'Subtasks can only be created in Story'
        );

        $issue = new Issue();
        $issue->setCode($this->get('tracker.issue.manager')->generateCode());
        $issue->setReporter($this->getUser());
        $issue->setParentIssue($parent);
        $typeFieldClassName = ExtendHelper::buildEnumValueClassName('issue_type');

        $typeSubtask = $this->getDoctrine()->getRepository($typeFieldClassName)->find('subtask');
        $issue->setType($typeSubtask);

        return $this->update($issue);
    }

    /**
     * @Route("/update/{id}", name="tracker.issue_update", requirements={"id":"\d+"}, defaults={"id":0})
     * @Template("VitaliiTrackerBundle:Issue:update.html.twig")
     * @Acl(
     *     id="tracker.issue_update",
     *     type="entity",
     *     class="VitaliiTrackerBundle:Issue",
     *     permission="EDIT"
     * )
     */
    public function updateAction(Issue $issue)
    {
        return $this->update($issue);
    }

    /**
     * @Route("/{id}", name="tracker.issue_view", requirements={"id":"\d+"}, defaults={"id":0})
     * @Template
     * @AclAncestor("tracker.issue_view")
     */
    public function viewAction(Issue $issue)
    {
        return [
            'entity' => $issue,
            'collaboratorsGrid' => 'issue-collaborators-grid',
            'subtasksGrid' => 'issue-subtasks-grid',
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="tracker.issue_widget_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("tracker.issue_view")
     */
    public function infoAction(Issue $entity)
    {
        return [
            'entity' => $entity,
        ];
    }

    /**
     * @Route("/widget/updated_at/{id}", name="tracker.issue_widget_updated_at", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("tracker.issue_view")
     */
    public function updatedAtAction(Issue $entity)
    {
        return [
            'updatedAt' => $entity->getUpdatedAt(),
        ];
    }

    /**
     * @Route("/delete/{id}", name="tracker.issue_delete", requirements={"id":"\d+"})
     * @Acl(
     *     id="tracker.issue_delete",
     *     type="entity",
     *     class="VitaliiTrackerBundle:Issue",
     *     permission="DELETE"
     * )
     */
    public function deleteAction(Issue $issue)
    {
        $this->delete($issue);

        return $this->redirectToRoute('tracker.issue_index');
    }

    /**
     * @Route("/dashboard/chart", name="tracker.issue_chart")
     * @Template("@VitaliiTracker/Dashboard/issuesChart.html.twig")
     * @AclAncestor("tracker.issue_view")
     */
    public function chartAction()
    {
        $viewBuilder = $this->container->get('oro_chart.view_builder');

        $datagrid = $this->get('oro_datagrid.datagrid.manager')->getDatagrid(
            'issues-chart-grid',
            [PagerInterface::PAGER_ROOT_PARAM => [PagerInterface::DISABLED_PARAM => true]]
        );

        $chartName = 'issue_line_chart';

        $view = $viewBuilder
            ->setDataGrid($datagrid)
            ->setOptions(
                array_merge_recursive(
                    [
                        'name' => 'bar_chart',  // this is actually a chart type, not name
                    ],
                    $this
                        ->get('oro_chart.config_provider')
                        ->getChartConfig($chartName)
                )
            )
            ->getView();

        return [
            'chartView' => $view,
        ];
    }

    private function update(Issue $issue)
    {
        $form = $this->get('form.factory')->create('tracker_issue', $issue);

        return  $this->get('oro_form.model.update_handler')->handleUpdate(
            $issue,
            $form,
            function (Issue $issue) {
                return [
                    'route' => 'tracker.issue_update',
                    'parameters' => ['id' => $issue->getId()]
                ];
            },
            function (Issue $issue) {
                $saveAndCloseRouteName = 'tracker.issue_index';
                $id = $issue->getId();
                if ($issue->getParentIssue()) {
                    $saveAndCloseRouteName = 'tracker.issue_view';
                    $id = $issue->getParentIssue()->getId();
                }
                return [
                    'route' => $saveAndCloseRouteName,
                    'parameters' => ['id' => $id]
                ];
            },
            $this->get('translator')->trans('vitalii.tracker.issue.saved')
        );
    }

    private function delete($issue)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($issue);
        $entityManager->flush();
    }
}
