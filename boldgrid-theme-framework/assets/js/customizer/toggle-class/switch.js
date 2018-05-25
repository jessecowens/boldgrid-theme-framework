/* esversion: 6 */
import ToggleClass from './toggle';

const api = wp.customize;
const $ = jQuery;

/**
 * This class is responsible for managing the expand and collapse
 * triggers for panels and sections in the WordPress customizer.
 *
 * @since 2.0.0
 */
export class ToggleClassSwitch extends ToggleClass {

	/**
	 * Handle the toggle of classes on an element.
	 *
	 * @2.0.0
	 *
	 * @param {Mixed} to New value control is updating to.
	 */
	toggle( to ) {
		to ? $( this.element ).addClass( this.remove ) : $( this.element ).removeClass( this.remove );
		this.cb();
	}
}

export default ToggleClassSwitch;
