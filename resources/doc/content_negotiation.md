Content negotiation
-------------------

For entity responses the following content negotiation is used:
* If available and valid use the request format.
* If available, not empty and valid use a content type from the `Accept` request header.
* Otherwise use the default content type (`application/json`, can be changed in the configuration).
