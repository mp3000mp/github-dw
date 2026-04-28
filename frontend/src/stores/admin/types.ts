import { AbstractState } from '@/stores/types'

export interface RoutineError {
  error: string
  date: string
  url: string
}
export interface Routine3Error extends RoutineError {
  path: string
}
export interface PackageTypeFiles {
  id: number
  file: string
  language: string
  name: string
  githubCurrentSize: number
  githubCurrentPage: number
  updatedAt: string
  priority: boolean
  count: number
}
export interface Stats {
  packageTypeFiles: { id: number; count: number }[]
  routines: {
    routine1Count: number
    routine2Count: number
    routine2DoneCount: number
    routine2ErrorCount: number
    routine3Count: number
    routine3DoneCount: number
    routine3ErrorCount: number
  }
}
export interface Routine1Timeline {
  label: string
  done: number
}
export interface RoutineTimeline extends Routine1Timeline {
  errors: number
}
export interface Timeline {
  labels: string[]
  routine1: Routine1Timeline[]
  routine2: RoutineTimeline[]
  routine3: RoutineTimeline[]
}

export class AdminState extends AbstractState {
  errors: { routine2: RoutineError[]; routine3: Routine3Error[] } = { routine2: [], routine3: [] }
  packageTypeFiles: PackageTypeFiles[] = []
  stats: Stats | null = null
  timeline: Timeline | null = null
}
