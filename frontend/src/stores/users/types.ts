import { AbstractState } from '@/stores/types'

export interface User {
  id: number
  email: string
  isEnabled: boolean
  roles: string[]
  username: string
}

export class UserState extends AbstractState {
  users: User[] = []
}
