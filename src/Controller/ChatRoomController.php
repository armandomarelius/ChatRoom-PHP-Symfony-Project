<?php 
namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Entity\ChatRoom;
use App\Form\ChatRoomType;
use App\Repository\ChatRoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MessageRepository;
use App\Entity\Message;
use App\Form\MessageType;

#[Route('/chat/room')]
final class ChatRoomController extends AbstractController
{
   
private function updateChatState(ChatRoomRepository $chatRoomRepository, EntityManagerInterface $entityManager): void
{
    $chats = $chatRoomRepository->findAll();

    foreach ($chats as $chat) {
        // Actualizamos el estado de cada chat
        $chat->updateState();
    }

    // Persistimos los cambios
    $entityManager->flush();
}


    #[Route(name: 'app_chat_room_index', methods: ['GET'])]
    public function index(ChatRoomRepository $chatRoomRepository, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $myUser = $this->getUser();

        $myUser->setChat(NULL);

        $entityManager->flush();

        // necsitamos los chatrooms filtrados
        $activeChatRooms = $chatRoomRepository->findActiveChats();

        // actualizamos el estado al cargar el index 
        $this->updateChatState($chatRoomRepository, $entityManager, $userRepository);

        return $this->render('chat_room/index.html.twig', [
            'activeChatRooms' => $activeChatRooms,
        ]);
    }

    #[Route('/new', name: 'app_chat_room_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ChatRoomRepository $chatRoomRepository, UserRepository $userRepository): Response
    {
        //seteamos a cero el estado 
        $chatRoom = new ChatRoom();
        $chatRoom->setState(0);

        $form = $this->createForm(ChatRoomType::class, $chatRoom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $myUser = $this->getUser();
            $myUser->setChat($chatRoom);

            $entityManager->persist($chatRoom);
            $entityManager->flush();

            // Llamamos a la función para actualizar el estado después de crear un nuevo chat
            $this->updateChatState($chatRoomRepository, $entityManager, $userRepository);

            return $this->redirectToRoute('app_chat_room_show', ['id' => $chatRoom->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chat_room/new.html.twig', [
            'chat_room' => $chatRoom,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_chat_room_show', methods: ['GET', 'POST'])]
public function show(
    Request $request,
    ChatRoom $chatRoom, 
    MessageRepository $messageRepository, 
    EntityManagerInterface $entityManager,
    ChatRoomRepository $chatRoomRepository, 
    UserRepository $userRepository
): Response {
    // Obtener los mensajes de la sala
    $messages = $messageRepository->findBy(['chatRoom' => $chatRoom]);

    // Obtener al usuario actual
    $myUser = $this->getUser();

    // Actualizar el chat del usuario al de la sala actual
    if ($myUser) {
        $myUser->setChat($chatRoom);
        $entityManager->flush();  // Guardar el cambio en la base de datos
    }

    // Crear un nuevo mensaje y asignarle valores
    $message = new Message();
    $message->setChatRoom($chatRoom);
    $message->setDate(new \DateTime());
    $message->setUser($myUser);

    // Crear el formulario con createForm()
    $form = $this->createForm(MessageType::class, $message);
    $form->handleRequest($request);

    // Guardar el mensaje si el formulario es válido
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($message);
        $entityManager->flush();

        $this->updateChatState($chatRoomRepository, $entityManager, $userRepository);

        return $this->redirectToRoute('app_chat_room_show', ['id' => $chatRoom->getId()]);
    }

    // Renderizar la vista
    return $this->render('chat_room/show.html.twig', [
        'chat_room' => $chatRoom,
        'messages' => $messages,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}/edit', name: 'app_chat_room_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ChatRoom $chatRoom, EntityManagerInterface $entityManager, ChatRoomRepository $chatRoomRepository, UserRepository $userRepository): Response
    {
        $form = $this->createForm(ChatRoomType::class, $chatRoom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_chat_room_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('chat_room/edit.html.twig', [
            'chat_room' => $chatRoom,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_chat_room_delete', methods: ['POST'])]
    public function delete(Request $request, ChatRoom $chatRoom, EntityManagerInterface $entityManager, ChatRoomRepository $chatRoomRepository, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chatRoom->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($chatRoom);
            $entityManager->flush();

        }

        return $this->redirectToRoute('app_chat_room_index', [], Response::HTTP_SEE_OTHER);
    }
}
