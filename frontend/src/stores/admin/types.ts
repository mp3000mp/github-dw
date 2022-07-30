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

export class AdminState extends AbstractState {
    packageTypeFiles: PackageTypeFiles[] = [];
    stats: {id: number, count: number}[] = [];
}
