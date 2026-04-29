import { describe, it, expect } from 'vitest';
import { ProjectMetadataSchema } from './index';

describe('ProjectMetadataSchema', () => {
  it('accepts a valid project metadata object', () => {
    const result = ProjectMetadataSchema.safeParse({ name: 'my-project' });
    expect(result.success).toBe(true);
  });

  it('rejects an empty name', () => {
    const result = ProjectMetadataSchema.safeParse({ name: '' });
    expect(result.success).toBe(false);
  });

  it('rejects a missing name', () => {
    const result = ProjectMetadataSchema.safeParse({});
    expect(result.success).toBe(false);
  });
});
