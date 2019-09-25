Datastore API
=============

Once a data file has been imported to the datastore you can run queries via the api.

.. code-block:: 
  
  http://dkan/api/v1/sql/[SELECT * FROM ${dataset_identifier}][WHERE state = 'OK'][ORDER BY county ASC][LIMIT 5 OFFSET 100]

