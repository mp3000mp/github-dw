import { AbstractState } from '@/stores/types'

export class Me {
  roles: string[] = ['ROLE_ANONYMOUS']
  username = 'Anonymous'
}

export class SecurityState extends AbstractState {
  me: Me = new Me()
}
