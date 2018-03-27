/* local storage operations */

/**
 * \brief	store key, value pair in local storage
 *
 * \param	key		identifier to be used
 * \param	value	string to be stored
 *
 * \return	nothing
 */
function set(key, value){
	localStorage.setItem(key, value);
}

/**
 * \brief	get value of key from local storage
 *
 * \param	key		identifier to be used
 *
 * \return	associated value
 */
function get(key){
	return localStorage.getItem(key);
}

/**
 * \brief	remove an entry from local storage
 *
 * \param	key		identifier to be removed
 *
 * \return	nothing
 */
function rm(key){
	localStorage.removeItem(key);
}
