# Filter Builder
This package was inspired by [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder). 
It's allows you to perform filtering, sorting and searching based on Eloquent query in API requests with a simple interface.  

# Navigation
1. [Installation](#installation)
2. [Basic usage](#basic-usage)
   1. [Filtering](#filter-based-on-request)
   2. [Sorting](#sort-based-on-request)
   3. [Search](#search-based-on-request)
   4. [Pagination](#pagination)
3. [Examples](#examples)

## Installation
You can install the package via composer:
```bash
composer require mykolab/filter-builder
```
## Basic usage
### Filter based on request
You can filter an Eloquent query with POST or GET requests.
Using POST requests you can send requests as json, form-data or different type.
Using GET requests you need just add filter parameters into query params, like:
`/users?name=John`.
To register allowed filters you need to add query (or Eloquent model class)
to FilterBuilder. And using method `allowedFilters` set array of allowed filters.
It should be instance of `AllowedFilter` or just a string which by default 
will be resolved as ExactAllowedFilter.

Example:

```php
use Mykolab\FilterBuilder\FilterBuilder;
use Mykolab\FilterBuilder\AllowedFilters\ExactAllowedFilter;

FilterBuilder::for(User::class)
    ->allowedFilters([
        'first_name',        
        ExactAllowedFilter::make('last_name'),        
    ]);
```
All available filters in the package has static `make` method. 
And all of them except `CallbackAllowedFilter` has required `name` and nullable `internalName`.
`name` is a parameter that you will use in API request and `internalName`
is Eloquent query field name by which you will perform filtering, if this field is `null`, by default,
it will be copied from `name` argument.
Example:

```php
use Mykolab\FilterBuilder\FilterBuilder;
use Mykolab\FilterBuilder\AllowedFilters\ExactAllowedFilter;

FilterBuilder::for($query)
    ->allowedFilters([
        ExactAllowedFilter::make('status', 'users.status'),        
    ]);
```
Also `internalName` can accept instance of `\Illuminate\Database\Query\Expression`
it is useful if you want filter by some raw expression (maybe event for `having` clause) like:
```php
\Illuminate\Support\Facades\DB::raw('sum(orders.price)')
```
#### Available filters:
1. ExactAllowedFilter - will add to Eloquent query `where` clauses with equal operator.
Equivalent to:
    ```php
    $query->where('name', request()->name)
    ```
    Also, you can use having clause by passing `$useHaving = true` argument in `make` method.
2. LikeAllowedFilter - will add to Eloquent query `where` clauses with `like` operator.
   Equivalent to:
    ```php
    $query->where('name', 'like', request()->name . '%')
    ```
    By default, this filter is case-sensitive. But you can specify case sensitivity for values with passing `$caseInsensitive`
    argument to `make` method, for example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedFilters\LikeAllowedFilter;

    FilterBuilder::for(User::class)
        ->allowedFilters([
            LikeAllowedFilter::make('name', caseInsensitive: true),        
        ]);
    ```
    By default, it has only percent wild-card in the end of value, like `request()->name . '%'`.
   But you can specify it with `wildCardAtStart` and `wildCardAtStart` methods.
   For example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedFilters\LikeAllowedFilter;

    FilterBuilder::for(User::class)
        ->allowedFilters([
            LikeAllowedFilter::make('name')->wildCardAtStart(false)->wildCardAtEnd(),        
        ]); 
    ```
   Also, you can use having clause by passing `$useHaving = true` argument in `make` method.
3. RangeAllowedFilter - will filter Eloquent query data between `from` and `to` parameters from API request.
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedFilters\RangeAllowedFilter;

    FilterBuilder::for(Order::class)
        ->allowedFilters([
            RangeAllowedFilter::make('price'),        
        ]);
    ```
    Equivalent to:
    ```php
    $query
        ->where('price', '>=', request()->price_from)
        ->where('price', '<=', request()->price_to)
    ```
    By default, you need to use `_from` and `_to` suffixes with allowed filter name,
   but you can change it in a `filter-builder.php` config file `request_parameters.range_suffix`.
   For example, if you registered `price` filter in API request it should looks like:
    `/orders?price_from=1&price_to=100`
    If you are using POST requests, alternatively you can just set `from` and `to` values as object parameters, like:
    ```json
    {
        "price": {
            "from": 1,
            "to": 100
        }
    }
    ```
    `from` and `to` are optional fields, if you will not set `to` parameter it will
   filter values that greater or equal `from` value, same logic if you will not set `from` parameter.
   Also, you can use having clause by passing `$useHaving = true` argument in `make` method.
4. DateRangeAllowedFilter - have the same functionality as `RangeAllowedFilter`.
   The difference is `DateRangeAllowedFilter` works with dates. You can round input parameters to `DateUnit` enum.
   For example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedFilters\DateRangeAllowedFilter;
    use Mykolab\FilterBuilder\Enums\DateUnit;

    FilterBuilder::for(Order::class)
        ->allowedFilters([
            DateRangeAllowedFilter::make('created_at')->roundDatesTo(DateUnit::HOUR),
        ]);
    ```
    Equivalent to:
    ```php
    use Carbon\Carbon;
    
    $query
        ->where('created_at', '>=', Carbon::parse(request()->created_at_from)->startOfHour())
        ->where('created_at', '<=', Carbon::parse(request()->created_at_to)->endOfHour())
    ```
   Also, you can use having clause by passing `$useHaving = true` argument in `make` method.
5. WhereInAllowedFilter - will add to Eloquent query `whereIn` clauses.
   For example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedFilters\WhereInAllowedFilter;

    FilterBuilder::for(User::class)
        ->allowedFilters([
            WhereInAllowedFilter::make('role'),
        ]);
    ```
    Equivalent to:
    ```php
    $query->whereIn('role', request()->get('status', []));
    ```
    You can set allowed options by using `allowedOptions` method and passing array of allowed values,
   all values that not in allowed options will be ignored during filtering.
   You can set multiple values for this allowed filter, if you are using GET request
   you can set it as array like `/users?role[]=customer&role[]=admin` or with delimiter as one parameter
   like `/users?role=customer,admin`, by default it is use comma delimiter, but you can specify your custom delimiter
   with calling `delimiter` method on `WhereInAllowedFilter` instance.
   If you are using POST requests with json, you can pass it as json array.  
6. CallbackAllowedFilter - will apply callback on Eloquent query.
   For example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedFilters\CallbackAllowedFilter;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Query\Expression;
    use Illuminate\Support\Facades\DB;

    FilterBuilder::for(User::class)
        ->allowedFilters([
            CallbackAllowedFilter::make(
                'role',
                function (Builder $query, Expression|string $property, mixed $value) {
                    $query->where('role', $value)->where('is_active', true)
                }
            ),
        ]);
    ```
If you want to create your custom allowed filter,
you need implement `\Mykolab\FilterBuilder\AllowedFilters\AllowedFilter` interface 
and you can use it in `allowedFilters` method.

### Sort based on request
You can sort an Eloquent query with POST or GET requests.
To register allowed sorting you need to add query (or Eloquent model class)
to FilterBuilder. And using method `allowedSorts` set array of allowed sorts,
which should be implement `Mykolab/Sorts/Sort` interface or be a string, which will be resolved
as `FieldSort`.
By default, sorting uses two parameters from request: `order_by` and `order_direction`,
they can be changed in `filter-builder.php` config file `request_parameters.order_by` and `request_parameters.order_direction`
Package allows sorting by field or by a callback.
#### Available sorting:
1. FieldSort - you need specify required `name` and nullable `internalName` in instance of `FieldSort` class.
`name` arguments will be use in `order_by` request and `internalName` is field in Eloquent query
by which will be applied sorting. If `internalName` argument is not set it will copy value from `name` argument.
`internalName` also can accept instance of `\Illuminate\Database\Query\Expression`.
For example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedSort;

    FilterBuilder::for(User::class)
        ->allowedSorts([
            AllowedSort::field('role'),
            AllowedSort::field('registered_at', 'created_at'),
        ]);
    ```
2. CallbackSort - can apply sorting according to given callback.
For example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedSort;
    use Illuminate\Database\Eloquent\Builder;
    use Mykolab\FilterBuilder\Enums\SortDirection;

    FilterBuilder::for(User::class)
        ->allowedSorts([
            AllowedSort::callback('created_at', function (Builder $query, string $property, SortDirection $sortDirection) {
                $query
                    ->orderBy($property, $sortDirection->value)
                    ->orderBy('id', $sortDirection->value);
            }),
        ]);
    ```
### Search based on request
You can perform search by multiple fields.
To register allowed searching you need to add query (or Eloquent model class)
to FilterBuilder. And using method `allowedSearch` set instance of `\Mykolab\FilterBuilder\AllowedSearch`,
And `AllowedSearch` accepts implementation of `\Mykolab\FilterBuilder\Search\Search` interface 
and `$searchParameterName` as second argument, it means what search parameter name it expect from request.
By default `$searchParameterName` is equal `search`, but you can change it in `filter-builder.php` config,
`request_parameters.search`.
#### Available search:
1. FieldSearch - filter Eloquent query by allowed fields.
Accepts array of `Mykolab\FilterBuilder\Search\Searchable` instances.
Searchable instance allows you to configure case-sensitivity and wildcards (percentage signs) at start and end of value.
By default, searchable instances are case-insensitive and have wildcards at the end of searched value.
But you can disable it with `disableWildCardAtStart`, `disableWildCardAtEnd`, `disableCaseInsensitive` methods.
Or enable by calling `wildCardAtStart()` or `wildCardAtEnd()` if needed. 
For example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedSearch;
    use Mykolab\FilterBuilder\Search\Searchable;

    FilterBuilder::for(User::class)
        ->allowedSearch(
            AllowedSearch::searchable([
                Searchable::make('first_name')->disableCaseInsensitive(),
                Searchable::make('last_name')->wildCardAtStart(),
                Searchable::make('email')->disableWildCardAtEnd(),
            ]),
        );
    ```
    Equivalent to:
    ```php
    use \Illuminate\Database\Eloquent\Builder;

    $query->where(function (Builder $query) {
        $query->where('first_name', 'ilike', request()->first_name . '%')
        $query->orWhere('last_name', 'like', '%' . request()->last_name . '%')
        $query->orWhere('email', 'like', request()->email);
    });
    ```
   All searchable instance will be converted into `orWhere` clauses and wrapped into `where`.

2. CallbackSearch - will apply callback to Eloquent query.
For example:
    ```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedSearch;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Support\Facades\DB;

    FilterBuilder::for(User::class)
        ->allowedSearch(
            AllowedSearch::callback(function (Builder $query, string $value) {
                $query->where(DB::raw("concat(first_name, ' ', last_name)"), 'like', $value . '%');
            }),
        );
    ```
    Equivalent to:
    ```php
    use Illuminate\Database\Eloquent\Builder;

    $query->where(function (Builder $query) {
        $query->where(DB::raw("concat(first_name, ' ', last_name)"), 'like', $value . '%');
    });
    ```
    Callback query will be wrapped into `where` clause.
### Pagination
If you want to paginate your Eloquent query data, you need to call `paginate` method on `FilterBuilder` instance.
By default, package using `\Mykolab\FilterBuilder\Pagination\Resolvers\SimplePaginationResolver` 
which returns `\Mykolab\FilterBuilder\Pagination\PaginationResource`.
But you can override it with `\Mykolab\FilterBuilder\Pagination\Resolvers\LaravelPaginationResolver` class in `filter-builder` config file in `default_pagination_resolver` key 
this will paginate your data with default Laravel paginators. Or you can create your own implementation by implementing `Mykolab\FilterBuilder\Pagination\Resolvers\PaginationResolver` interface
and putting that class in config file.
By default, for pagination used `page` and `per_page` parameters in request.
`page` parameter is for what page need to show and `per_page` is for number of items per page.
They can be changed in `filter-builder.php` config file, `request_parameters.current_page` 
and `request_parameters.per_page` keys.
Default per_page value is 10, it can be changed from `filter-builder` config file, `default_per_page` key.
Example:
```php
    use Mykolab\FilterBuilder\FilterBuilder;

    return FilterBuilder::for(User::class)
        ->allowedSorts([
            'name',
            'email',
        ])
        ->allowedFilters([
            'name',
            'email',
        ])->paginate();
```
This will return's all users non-hidden attributes.
#### Applying json resources
If you want to specify fields for pagination, you can apply json resource.
And instead of array with model attributes, you will receive json resource data.
Example:
```php
    use Mykolab\FilterBuilder\FilterBuilder;

    return FilterBuilder::for(User::class)
        ->resource(UserResource::class)
        ->allowedSorts([
            'name',
            'email',
        ])
        ->allowedFilters([
            'name',
            'email',
        ])->paginate();
```

Also from `FilterBuilder` instance you can get Eloquent query instance with applied filters, sorting and searches
by calling `getQueryBuilder` method.
And you can get all filtered data by calling `get` method.

## Examples
```php
    use Mykolab\FilterBuilder\FilterBuilder;
    use Mykolab\FilterBuilder\AllowedSearch;
    use Illuminate\Support\Facades\DB;
    use Mykolab\FilterBuilder\AllowedSearch;
    use Mykolab\FilterBuilder\Search\Searchable;
    use Mykolab\FilterBuilder\AllowedSort;
    use Mykolab\FilterBuilder\AllowedFilters\DateRangeAllowedFilter;
    use Mykolab\FilterBuilder\AllowedFilters\RangeAllowedFilter;
    use Mykolab\FilterBuilder\AllowedFilters\WhereInAllowedFilter;
    use Mykolab\FilterBuilder\AllowedFilters\ExactAllowedFilter;
    use Mykolab\FilterBuilder\Enums\DateUnit;

    public function index()
    {
        $query = Product::query()
            ->select('products.*', 'c.name as category_name')
            ->join('categories as c', 'c.id', '=', 'products.category_id');

        return FilterBuilder::for($query)
            ->resource(ProductResource::class)
            ->allowedSorts([
                AllowedSort::field('id', 'products.id'),
                AllowedSort::field('category_name'),
                AllowedSort::field('price'),
            ])
            ->allowedFilters([
                ExactAllowedFilter::make('name', 'products.name'),
                DateRangeAllowedFilter::make('created', 'products.created_at')->roundDatesTo(DateUnit::HOUR),
                RangeAllowedFilter::make('price'),
                WhereInAllowedFilter::make('status', 'products.status')->allowedOptions(['published', 'preorder']),
            ])
            ->allowedSearch(
                AllowedSearch::searchable([
                    Searchable::make('products.id')->disableWildCardAtEnd(),
                    Searchable::make('products.name')->disableCaseInsensitive(),
                    Searchable::make('categories.name'),
                ])
            )
            ->paginate();
    }
```
In this example, this query will accept order_by values: `id, category_name, price`.

For filtering it will accept: `name, created_from, created_to, price_from, price_to, status` (only for published and preorder values),
all other values and parameters will be skipped.

If request will have `search` parameter, it will apply `orWhere` condition and search for `products.id`, `products.name`, `categories.name`.

And paginated data will be wrapped with `ProductResource` json resource.
