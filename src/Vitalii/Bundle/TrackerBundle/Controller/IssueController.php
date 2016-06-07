<?php

namespace Vitalii\Bundle\TrackerBundle\Controller;

use BeSimple\SoapBundle\ServiceDefinition\Annotation\Param;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     *     id="tracker.issue_view_acl",
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
        $issue->setReporter($this->getUser());

        return $this->update($issue, $request);
    }

    /**
     * @Route("/subtask/{issue_code}", name="tracker.issue_subtask", requirements={"issue_code":".+"})
     * @ParamConverter("issue", options={
     *    "mapping": {"issue_code": "code"}
     * })
     * @Template("VitaliiTrackerBundle:Issue:update_subtask.html.twig")
     * @Acl(
     *     id="tracker.issue_create",
     *     type="entity",
     *     class="VitaliiTrackerBundle:Issue",
     *     permission="CREATE"
     * )
     */
    public function addSubtaskAction(Issue $parent, Request $request)
    {
        $this->denyAccessUnlessGranted(
            'subtask',
            $parent,
            'Subtasks can only be created in Story'
        );

        $issue = new Issue();
        $issue->setReporter($this->getUser());
        $issue->setParentIssue($parent);
        $typeFieldClassName = ExtendHelper::buildEnumValueClassName('issue_type');

        $typeSubtask = $this->getDoctrine()->getRepository($typeFieldClassName)->find('subtask');
        $issue->setType($typeSubtask);

        return $this->update(
            $issue,
            $request,
            'tracker_issue_subtask',
            [
                'route' => 'tracker.issue_view',
                'parameters' => array('issue_code' => $parent->getCode()),
            ]
        );
    }

    /**
     * @Route("/update/{issue_code}", name="tracker.issue_update", requirements={"issue_code":".+"})
     * @ParamConverter("issue", options={
     *    "mapping": {"issue_code": "code"}
     * })
     * @Acl(
     *     id="tracker.issue_update",
     *     type="entity",
     *     class="VitaliiTrackerBundle:Issue",
     *     permission="EDIT"
     * )
     */
    public function updateAction(Issue $issue, Request $request)
    {
        $formName = 'tracker_issue';
        $template = 'VitaliiTrackerBundle:Issue:update.html.twig';

        if (!empty($issue->getParentIssue())) {
            $formName = 'tracker_issue_subtask';
            $template = 'VitaliiTrackerBundle:Issue:update_subtask.html.twig';
        } elseif (!$issue->getChildIssues()->isEmpty()) {
            $formName = 'tracker_issue_story';
            $template = 'VitaliiTrackerBundle:Issue:update_story.html.twig';
        }

        $data = $this->update($issue, $request, $formName);

        if (is_array($data)) {
            return $this->render($template, $data);
        } else {
            return $data;
        }
    }

    /**
     * @Route("/{id}", name="tracker.issue_view", requirements={"id":"\d+"}, defaults={"id":0})
     * @Template
     * @AclAncestor("tracker.issue_view_acl")
     */
//    public function viewAction(Issue $issue)
//    {
//        return [
//            'entity' => $issue,
//            'collaboratorsGrid' => 'issue-collaborators-grid',
//            'subtasksGrid' => 'issue-subtasks-grid',
//        ];
//    }

    /**
     * @Route("/{issue_code}", name="tracker.issue_view", requirements={"issue_code":".+"}, defaults={"issue_code":0})
     * @ParamConverter("issue", options={
     *    "mapping": {"issue_code": "code"}
     * })
     * @Template
     * @AclAncestor("tracker.issue_view_acl")
     * @Param Issue $issue
     * @return array
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
     * @Route("/widget/info/{issue_code}", name="tracker.issue_widget_info", requirements={"issue_code"=".+"})
     * @ParamConverter("issue", options={
     *    "mapping": {"issue_code": "code"}
     * })
     * @Template
     * @AclAncestor("tracker.issue_view_acl")
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
     * @AclAncestor("tracker.issue_view_acl")
     * @param Issue $issue
     * @return array
     */
    public function updatedAtAction(Issue $issue)
    {
        return [
            'updatedAt' => $issue->getUpdatedAt(),
        ];
    }

    /**
     * @Route("/delete/{issue_code}", name="tracker.issue_delete", requirements={"issue_code":".+"})
     * @ParamConverter("issue", options={
     *    "mapping": {"issue_code": "code"}
     * })
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
     * @AclAncestor("tracker.issue_view_acl")
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

    private function update(Issue $issue, Request $request, $formName = 'tracker_issue', $redirectRoute = [])
    {
        $form = $this->get('form.factory')->create($formName, $issue);
        $form->handleRequest($request);

        if (empty($redirectRoute)) {
            $redirectRoute = [
                'route' => 'tracker.issue_index'
            ];
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($issue);
            $entityManager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                [
                    'route' => 'tracker.issue_update',
                    'parameters' => array('issue_code' => $issue->getCode()),
                ],
                $redirectRoute,
                $issue
            );
        }

        return array(
            'entity' => $issue,
            'form' => $form->createView(),
        );
    }

    private function delete($issue)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($issue);
        $entityManager->flush();
    }
}
