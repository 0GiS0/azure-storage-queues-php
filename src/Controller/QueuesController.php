<?php

namespace App\Controller;

use App\Service\QueueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QueuesController extends AbstractController
{
    /**
     * @Route("/", name="queues")
     */
    public function index(QueueService $messaging)
    {
        return $this->render('queues/index.html.twig', [
            'controller_name' => 'QueuesController',
            'messages' => $messaging->peekMessages(),
        ]);
    }

    /**
     * @Route("/message")
     */
    public function getMessage(QueueService $messaging)
    {
        $message = $messaging->getMessage();
        return new JsonResponse(array(
            'id' => $message[0]->getMessageId(),
            'popReceipt' => $message[0]->getPopReceipt(),
            'text' => $message[0]->getMessageText(),
            'insertionDate' => $message[0]->getInsertionDate(),
            'dequeueCount' => $message[0]->getDequeueCount(),
        ));
    }

    /**
     * @Route("/message/delete/{id}/{popReceipt}")
     */
    public function delete($id, $popReceipt, QueueService $messaging)
    {
        $messaging->deleteMessage($id, $popReceipt);
        return $this->redirectToRoute('queues');
    }

    /**
     * @Route("/send/message")
     */
    public function send(Request $request, QueueService $messaging)
    {
        $message = $request->get('message');
        $messaging->sendMessage($message);
        return $this->redirectToRoute('queues');
    }
}
