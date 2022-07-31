import { AbstractState } from '@/stores/types'

export interface PackageTypeFiles {
    id: number;
    file: string;
    language: string;
    name: string;
    githubCurrentSize: number;
    githubCurrentPage: number;
    updatedAt: string;
    priority: boolean;
    count?: number;
}
export interface Stats {
    packageTypeFiles: {id: number, count: number}[],
    routines: {
        routine1Count: number;
        routine2Count: number;
        routine2DoneCount: number;
        routine2ErrorCount: number;
        routine3Count: number;
        routine3DoneCount: number;
        routine3ErrorCount: number;
    }
}

export class AdminState extends AbstractState {
    packageTypeFiles: PackageTypeFiles[] = [];
    stats: Stats|null = null;
}
