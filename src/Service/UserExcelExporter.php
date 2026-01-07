<?php
namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Repository\UserRepository;

class UserExcelExporter
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function export(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        
        // Set the header row
        $sheet->setCellValue('A1', 'Name')
              ->setCellValue('B1', 'Lastname')
              ->setCellValue('C1', 'Email')
              ->setCellValue('D1', 'Role')
              ->setCellValue('E1', 'Created At')
              ->setCellValue('F1', 'Ban Status');


              $sheet->getStyle('A1:F1')->getFont()->setBold(true);

              // Auto-size columns
              foreach (range('A', 'F') as $col) {
                  $sheet->getColumnDimension($col)->setAutoSize(true);
              }
        // Fetch users
        $users = $this->userRepository->findAll();

        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue('A' . $row, $user->getName());
            $sheet->setCellValue('B' . $row, $user->getLastname());
            $sheet->setCellValue('C' . $row, $user->getEmail());
            $sheet->setCellValue('D' . $row, implode(', ', $user->getRoles()));
            $sheet->setCellValue('E' . $row, $user->getDateCreation()->format('Y-m-d'));
            $sheet->setCellValue('F' . $row, $user->getIsBanned() ? 'Banned' : 'Active');
            $row++;
        }

        // Stream the file as a response
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="users.xlsx"');

        return $response;
    }
}
