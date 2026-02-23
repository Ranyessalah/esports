<?php

namespace App\Controller;

use App\Entity\Attendance;
use App\Entity\Notification;
use App\Entity\Training;
use App\Repository\AttendanceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/attendance')]
#[IsGranted('ROLE_COACH')]
class AttendanceController extends AbstractController
{
    #[Route('/', name: 'app_attendance_index')]
    public function index(AttendanceRepository $attendanceRepository): Response
    {
        return $this->render('attendance/index.html.twig', [
            'attendances' => $attendanceRepository->findBy([], ['recordedAt' => 'DESC']),
        ]);
    }

    #[Route('/training/{id}', name: 'app_admin_attendance_take', methods: ['GET', 'POST'])]
    public function take(Training $training, UserRepository $userRepository, AttendanceRepository $attendanceRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get existing attendances for this training
        $existingAttendances = $attendanceRepository->findBy(['training' => $training]);
        $attendanceMap = [];
        $players = [];
        
        foreach ($existingAttendances as $att) {
            $attendanceMap[$att->getPlayer()->getId()] = $att;
            $players[] = $att->getPlayer();
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all('attendance');
            
            foreach ($players as $player) {
                $status = $data[$player->getId()]['status'] ?? null;
                $comment = $data[$player->getId()]['comment'] ?? null;

                if ($status) {
                    $attendance = $attendanceMap[$player->getId()] ?? new Attendance();
                    $attendance->setTraining($training);
                    $attendance->setPlayer($player);
                    $attendance->setStatus($status);
                    $attendance->setComment($comment);
                    $attendance->setRecordedAt(new \DateTimeImmutable());
                    
                    $entityManager->persist($attendance);

                    // Create Notification for the player
                    $notification = new Notification();
                    $notification->setRecipient($player);
                    $notification->setType('attendance');
                    $msg = sprintf(
                        'Your attendance for "%s" (%s) has been marked as %s.',
                        $training->getTitle(),
                        $training->getDate()->format('Y-m-d'),
                        strtoupper($status)
                    );
                    if ($comment) {
                        $msg .= ' Coach comment: ' . $comment;
                    }
                    $notification->setMessage($msg);
                    $entityManager->persist($notification);
                }
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Attendance updated successfully.');
            
            return $this->redirectToRoute('app_training_show', ['id' => $training->getId()]);
        }

        return $this->render('attendance/take.html.twig', [
            'training' => $training,
            'players' => $players,
            'attendanceMap' => $attendanceMap,
        ]);
    }
}
