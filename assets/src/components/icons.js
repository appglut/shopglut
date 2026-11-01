import {
  faBagShopping, faBullhorn, faCartShopping, faCircleCheck, faEye, faFilter, faHeart, faListCheck, faPalette,
  faPenToSquare, faPuzzlePiece, faScaleBalanced, faShirt, faStore, faTags,
} from '@fortawesome/free-solid-svg-icons';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '../extend';

const ICONS = {
  faBagShopping, faBullhorn, faCartShopping, faCircleCheck, faEye, faFilter, faHeart, faListCheck, faPalette,
  faPenToSquare, faPuzzlePiece, faScaleBalanced, faShirt, faStore, faTags,
};

/** Font Awesome icon of a module (PHP gives its name). Add-ons add icons with the shopglut.icons filter. */
export function moduleIcon(name) {
  const icons = applyFilters('shopglut.icons', ICONS);
  return icons[name] || faPuzzlePiece;
}

export function categoryLabel(key) {
  return {
    builders: __( 'Page Builders', 'shopglut' ),
    product: __( 'Product Enhancements', 'shopglut' ),
    cart: __( 'Cart & Checkout', 'shopglut' ),
    engagement: __( 'Engagement', 'shopglut' ),
  }[key] || key;
}
