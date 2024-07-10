<?php

namespace App\Repositories\Eloquent;

use App\Models\ContactUs;
use App\Models\Example;
use App\Models\FaqHome;
use App\Models\MdPrivacy;
use App\Models\MdTerm;
use App\Models\Product;
use App\Repositories\Contracts\HelpRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HelpRepository implements HelpRepositoryInterface
{
    public function faqHome($request)
    {
        $search = $request->input('search');
        $paginate = $request->input('paginate', 10);

        $query = FaqHome::query();

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhere('category', 'like', '%' . $search . '%');
        }

        return $query->paginate($paginate);
    }


    public function faqProfile($request)
    {
        //
    }

    public function contactUs($request, $id)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $category = $request->input('category');
        $question = $request->input('question');
        $save = new ContactUs();
        $save->users_id = $id;
        $save->name = $name;
        $save->email = $email;
        $save->category = $category;
        $save->question = $question;
        $save->save();

        return $save;
    }


    public function term()
    {
        return MdTerm::first();
    }

    public function privacy()
    {
        return MdPrivacy::first();
    }
}
