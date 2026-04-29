import { useAppStore } from '@/store';

export function App() {
  const appName = useAppStore((state) => state.appName);
  return <h1>Hello {appName}</h1>;
}
