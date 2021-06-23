# Laravel DataTable

Laravel DataTable is a wrapper class for Yajra DataTable, this class is intended to separate queries and config on yajra datatable and call blade view as well as function as json
responders when called with ajax.

## Installation

To install the package, simply follow the steps below.

Install the package using Composer:

```
$ composer require octopyid/laravel-datatable
```

## Usage

##### UserDataTable.php

```php
<?php

namespace App\DataTables;

use App\Models\User;
use Octopy\DataTable\DataTable;
use Yajra\DataTables\DataTableAbstract;

class UserDataTable extends DataTable
{
    /**
     * @param  FooBarBaz $baz
     * @return mixed
     */
    public function query(FooBarBaz $baz)
    {
        return User::query();
    }

    /**
     * @param  DataTableAbstract $table
     */
    public function option(DataTableAbstract $table) : void
    {
        //
    }
}
```

##### UserController.php

```php
<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;

class UserController extends Controller 
{
    /**
     * @param UserDataTable $table
     * @return mixed
     */
    public function index(UserDataTable $table)
    {
        return $table->render('user.datatable', [
            'foo' => 'Bar Baz'
        ]);    
    }
}
```

## Explanation

### query

In the `query` method, it is used only to relate to the database or from other data, and may only return data instances of Collection, Eloquent or Query Builder.

You are free to pass other classes to this method, it will be injected automatically.

### option

And in the `option` method, you are free to modify the results, like column editing, row editing or etc,
see [Yajra DataTable Doc](https://yajrabox.com/docs/laravel-datatables/master/).

## Security

If you discover any security related issues, please email [supianidz@gmail.com](mailto:supianidz@gmail.com) or [supianidz@octopy.id](mailto:me@octopy.id) instead of using the issue
tracker.

## Credits

- [Supian M](https://github.com/SupianIDz)
- [Octopy ID](https://github.com/OctopyID)

## License

The MIT License (MIT). Please see [License File](https://github.com/SupianIDz/LaraDataTable/blob/master/LICENSE) for more information.

## Change Logs

### v1.0.0

- Initial release.
