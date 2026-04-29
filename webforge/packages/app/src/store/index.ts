import { create } from 'zustand';
import { immer } from 'zustand/middleware/immer';
import { ProjectMetadataSchema } from '@webforge/shared-types';

interface AppState {
  appName: string;
  setAppName: (name: string) => void;
}

const initialMetadata = ProjectMetadataSchema.parse({ name: 'WebForge' });

export const useAppStore = create<AppState>()(
  immer((set) => ({
    appName: initialMetadata.name,
    setAppName: (name) => {
      set((state) => {
        state.appName = name;
      });
    },
  })),
);
