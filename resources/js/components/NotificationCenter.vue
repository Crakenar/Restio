<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import { Bell, Check } from 'lucide-vue-next'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

interface Notification {
  id: string
  type: string
  data: {
    message: string
    vacation_request_id?: number
    employee_name?: string
    type?: string
    start_date?: string
    end_date?: string
  }
  read_at: string | null
  created_at: string
  created_at_human: string
}

interface NotificationsResponse {
  notifications: Notification[]
  unread_count: number
}

const toast = useToast()
const notifications = ref<Notification[]>([])
const unreadCount = ref(0)
const isOpen = ref(false)
const isLoading = ref(false)

const hasUnread = computed(() => unreadCount.value > 0)

async function fetchNotifications() {
  isLoading.value = true
  try {
    const response = await fetch('/notifications')
    const data: NotificationsResponse = await response.json()
    notifications.value = data.notifications
    unreadCount.value = data.unread_count
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    isLoading.value = false
  }
}

async function markAsRead(notificationId: string) {
  try {
    await router.post(`/notifications/${notificationId}/read`, {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        fetchNotifications()
      },
    })
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

async function markAllAsRead() {
  try {
    await router.post('/notifications/read-all', {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        fetchNotifications()
        toast.success('All notifications marked as read!')
      },
      onError: () => {
        toast.error('Failed to mark notifications as read.')
      },
    })
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error)
  }
}

function getNotificationIcon(type: string) {
  switch (type) {
    case 'VacationRequestSubmitted':
      return '📝'
    case 'VacationRequestApproved':
      return '✅'
    case 'VacationRequestRejected':
      return '❌'
    default:
      return '🔔'
  }
}

function getNotificationColor(type: string, isRead: boolean) {
  if (isRead) {
    return 'text-gray-500 dark:text-gray-400'
  }

  switch (type) {
    case 'VacationRequestSubmitted':
      return 'text-blue-600 dark:text-blue-400'
    case 'VacationRequestApproved':
      return 'text-green-600 dark:text-green-400'
    case 'VacationRequestRejected':
      return 'text-red-600 dark:text-red-400'
    default:
      return 'text-gray-700 dark:text-gray-300'
  }
}

onMounted(() => {
  fetchNotifications()

  // Refresh notifications every 30 seconds
  const interval = setInterval(fetchNotifications, 30000)

  // Cleanup on unmount
  return () => clearInterval(interval)
})
</script>

<template>
  <DropdownMenu v-model:open="isOpen">
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        size="icon"
        class="relative"
        :class="hasUnread ? 'text-orange-600 dark:text-orange-400' : ''"
      >
        <Bell class="h-5 w-5" />
        <span
          v-if="hasUnread"
          class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-orange-500 text-[10px] font-bold text-white"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
      </Button>
    </DropdownMenuTrigger>

    <DropdownMenuContent align="end" class="w-80">
      <div class="flex items-center justify-between px-4 py-3 border-b">
        <h3 class="font-semibold text-sm">Notifications</h3>
        <Button
          v-if="hasUnread"
          variant="ghost"
          size="sm"
          class="h-auto p-0 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
          @click="markAllAsRead"
        >
          Mark all as read
        </Button>
      </div>

      <div v-if="isLoading" class="p-4 text-center text-sm text-gray-500">
        Loading notifications...
      </div>

      <div
        v-else-if="notifications.length === 0"
        class="p-8 text-center text-sm text-gray-500"
      >
        <Bell class="h-12 w-12 mx-auto mb-2 opacity-20" />
        <p>No notifications yet</p>
      </div>

      <div v-else class="max-h-96 overflow-y-auto">
        <DropdownMenuItem
          v-for="notification in notifications"
          :key="notification.id"
          class="flex flex-col items-start gap-1 p-4 cursor-pointer transition-colors"
          :class="notification.read_at ? 'opacity-60' : 'bg-orange-50/50 dark:bg-orange-950/20'"
          @click="markAsRead(notification.id)"
        >
          <div class="flex items-start gap-2 w-full">
            <span class="text-xl flex-shrink-0">{{ getNotificationIcon(notification.type) }}</span>
            <div class="flex-1 min-w-0">
              <p
                class="text-sm font-medium leading-tight"
                :class="getNotificationColor(notification.type, !!notification.read_at)"
              >
                {{ notification.data.message }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ notification.created_at_human }}
              </p>
            </div>
            <Check
              v-if="notification.read_at"
              class="h-4 w-4 text-green-500 flex-shrink-0"
            />
          </div>
        </DropdownMenuItem>
      </div>

      <DropdownMenuSeparator v-if="notifications.length > 0" />

      <div v-if="notifications.length > 0" class="p-2">
        <Button
          variant="ghost"
          size="sm"
          class="w-full justify-center text-xs"
          @click="() => { isOpen = false; router.visit('/requests') }"
        >
          View all requests
        </Button>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
