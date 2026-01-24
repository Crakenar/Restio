import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';
import type { Config, DriveStep } from 'driver.js';
import { useI18n } from 'vue-i18n';

type UserRole = 'employee' | 'manager' | 'admin';

export function useOnboardingTour() {
    const { t } = useI18n();

    const getDriverConfig = (role: UserRole): Config => ({
        showProgress: true,
        animate: true,
        opacity: 0.75,
        smoothScroll: true,
        allowClose: true,
        overlayClickNext: false,
        stagePadding: 10,
        stageRadius: 10,
        popoverClass: 'restio-tour-popover',
        progressText: '{{current}} / {{total}}',
        nextBtnText: t('tour.next'),
        prevBtnText: t('tour.previous'),
        doneBtnText: t('tour.done'),
        onDestroyed: () => {
            localStorage.setItem(`onboarding_tour_${role}_completed`, 'true');
        },
    });

    // Employee Tour Steps
    const employeeTourSteps: DriveStep[] = [
        {
            element: '[data-tour="welcome"]',
            popover: {
                title: t('tour.employee.welcome.title'),
                description: t('tour.employee.welcome.description'),
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '[data-tour="stats"]',
            popover: {
                title: t('tour.employee.stats.title'),
                description: t('tour.employee.stats.description'),
                side: 'bottom',
                align: 'center',
            },
        },
        {
            element: '[data-tour="request-button"]',
            popover: {
                title: t('tour.employee.requestButton.title'),
                description: t('tour.employee.requestButton.description'),
                side: 'left',
                align: 'start',
            },
        },
        {
            element: '[data-tour="upcoming"]',
            popover: {
                title: t('tour.employee.upcoming.title'),
                description: t('tour.employee.upcoming.description'),
                side: 'top',
                align: 'center',
            },
        },
        {
            element: '[data-tour="sidebar"]',
            popover: {
                title: t('tour.employee.sidebar.title'),
                description: t('tour.employee.sidebar.description'),
                side: 'right',
                align: 'start',
            },
        },
        {
            popover: {
                title: t('tour.employee.complete.title'),
                description: t('tour.employee.complete.description'),
            },
        },
    ];

    // Manager Tour Steps
    const managerTourSteps: DriveStep[] = [
        {
            element: '[data-tour="welcome"]',
            popover: {
                title: t('tour.manager.welcome.title'),
                description: t('tour.manager.welcome.description'),
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '[data-tour="pending-requests"]',
            popover: {
                title: t('tour.manager.pendingRequests.title'),
                description: t('tour.manager.pendingRequests.description'),
                side: 'bottom',
                align: 'center',
            },
        },
        {
            element: '[data-tour="approve-reject"]',
            popover: {
                title: t('tour.manager.approveReject.title'),
                description: t('tour.manager.approveReject.description'),
                side: 'left',
                align: 'start',
            },
        },
        {
            element: '[data-tour="team-overview"]',
            popover: {
                title: t('tour.manager.teamOverview.title'),
                description: t('tour.manager.teamOverview.description'),
                side: 'top',
                align: 'center',
            },
        },
        {
            element: '[data-tour="sidebar"]',
            popover: {
                title: t('tour.manager.sidebar.title'),
                description: t('tour.manager.sidebar.description'),
                side: 'right',
                align: 'start',
            },
        },
        {
            popover: {
                title: t('tour.manager.complete.title'),
                description: t('tour.manager.complete.description'),
            },
        },
    ];

    // Admin Tour Steps
    const adminTourSteps: DriveStep[] = [
        {
            element: '[data-tour="welcome"]',
            popover: {
                title: t('tour.admin.welcome.title'),
                description: t('tour.admin.welcome.description'),
                side: 'bottom',
                align: 'start',
            },
        },
        {
            element: '[data-tour="analytics"]',
            popover: {
                title: t('tour.admin.analytics.title'),
                description: t('tour.admin.analytics.description'),
                side: 'bottom',
                align: 'center',
            },
        },
        {
            element: '[data-tour="employee-management"]',
            popover: {
                title: t('tour.admin.employeeManagement.title'),
                description: t('tour.admin.employeeManagement.description'),
                side: 'left',
                align: 'start',
            },
        },
        {
            element: '[data-tour="requests-table"]',
            popover: {
                title: t('tour.admin.requestsTable.title'),
                description: t('tour.admin.requestsTable.description'),
                side: 'top',
                align: 'center',
            },
        },
        {
            element: '[data-tour="sidebar"]',
            popover: {
                title: t('tour.admin.sidebar.title'),
                description: t('tour.admin.sidebar.description'),
                side: 'right',
                align: 'start',
            },
        },
        {
            element: '[data-tour="settings"]',
            popover: {
                title: t('tour.admin.settings.title'),
                description: t('tour.admin.settings.description'),
                side: 'left',
                align: 'start',
            },
        },
        {
            popover: {
                title: t('tour.admin.complete.title'),
                description: t('tour.admin.complete.description'),
            },
        },
    ];

    const startEmployeeTour = () => {
        const driverObj = driver({
            ...getDriverConfig('employee'),
            steps: employeeTourSteps,
        });
        driverObj.drive();
    };

    const startManagerTour = () => {
        const driverObj = driver({
            ...getDriverConfig('manager'),
            steps: managerTourSteps,
        });
        driverObj.drive();
    };

    const startAdminTour = () => {
        const driverObj = driver({
            ...getDriverConfig('admin'),
            steps: adminTourSteps,
        });
        driverObj.drive();
    };

    const startTourForRole = (role: UserRole) => {
        switch (role) {
            case 'employee':
                startEmployeeTour();
                break;
            case 'manager':
                startManagerTour();
                break;
            case 'admin':
                startAdminTour();
                break;
        }
    };

    const hasSeenTour = (role: UserRole): boolean => {
        return localStorage.getItem(`onboarding_tour_${role}_completed`) === 'true';
    };

    const resetTour = (role?: UserRole) => {
        if (role) {
            localStorage.removeItem(`onboarding_tour_${role}_completed`);
        } else {
            // Reset all tours
            localStorage.removeItem('onboarding_tour_employee_completed');
            localStorage.removeItem('onboarding_tour_manager_completed');
            localStorage.removeItem('onboarding_tour_admin_completed');
        }
    };

    const shouldShowTour = (role: UserRole): boolean => {
        return !hasSeenTour(role);
    };

    return {
        startEmployeeTour,
        startManagerTour,
        startAdminTour,
        startTourForRole,
        hasSeenTour,
        resetTour,
        shouldShowTour,
    };
}
