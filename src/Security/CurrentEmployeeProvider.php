<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;

class CurrentEmployeeProvider
{
    public function __construct(
        private readonly EmployeeRepository $employeeRepository
    ) {}

    public function getCurrentEmployee(): ?Employee
    {
        // @todo Implement getting current authenticated employee
        // currently it's not in task scope so I take random employee
        return $this->employeeRepository->findOneBy([]);
    }
}
