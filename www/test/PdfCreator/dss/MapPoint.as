package drake.dss
{
	class MapPoint  
	{
		private var keys :Array;
		private var items:Array;
		public function MapPoint ()
		{
			keys = new Array();
			items = new Array();
		}
		//---------------------------------
		public function getPoint(key:String)
		{
	//	trace ("keys.length == " + keys.length);
			var i = this.indexOf(key);
			if (i > -1) return items[i];
			i = keys.length;
			keys.push(key);
			items.push({x:-100000, y:-100000});
			return items[i];
		}
		//----------------------
		public function insert(key:String, point:Object)
		{
			var i = this.indexOf(key);
			if (i > -1) return items[i];
			i = keys.length;
			keys.push(key);
			items.push(point);
			return items[i];
		}
		//---------------------------------
		public function indexOf(key:String):Number
		{
			var i:Number = 0;
			while (i < keys.length)
			{
			 if (keys[i] == key) return i;
				i++;
			}
			return -1;
		}
	}
}