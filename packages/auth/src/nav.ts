import type { NavItem } from '@mema/ui';

export function filterNavItems(
  items: NavItem[],
  can: (permission: string) => boolean
): NavItem[] {
  return items.filter((item) => {
    if (item.permission && !can(item.permission)) {
      return false;
    }
    if (item.anyPermission && !item.anyPermission.some((permission) => can(permission))) {
      return false;
    }
    return true;
  });
}
