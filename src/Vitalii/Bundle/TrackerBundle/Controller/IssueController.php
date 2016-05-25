<?php

namespace Vitalii\Bundle\TrackerBundle\Controller;

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
        return array('gridName' => 'issues-grid');
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
     * @Route("/subtask/{id}", name="tracker.issue_subtask")
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

        return $this->update($issue, $request, 'tracker_issue_subtask');
    }

    /**
     * @Route("/update/{id}", name="tracker.issue_update", requirements={"id":"\d+"}, defaults={"id":0})
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
     * @AclAncestor("tracker.issue_view")
     */
    public function viewAction(Issue $issue)
    {
        return [
            'entity' => $issue,
            'collaboratorsGrid' => 'issue-collaborators-grid'
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

    private function update(Issue $issue, Request $request, $formName = 'tracker_issue')
    {
        $form = $this->get('form.factory')->create($formName, $issue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($issue);
            $entityManager->flush();

            return $this->get('oro_ui.router')->redirectAfterSave(
                array(
                    'route' => 'tracker.issue_update',
                    'parameters' => array('id' => $issue->getId()),
                ),
                array('route' => 'tracker.issue_index'),
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
